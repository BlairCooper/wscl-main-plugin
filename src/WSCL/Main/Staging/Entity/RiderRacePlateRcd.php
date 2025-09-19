<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

class RiderRacePlateRcd extends RacePlateRcd
{
    const PHONE_PATTERN = "/[\\(]?([0-9]{3})[\\)]?[-]?([0-9]{3})[-]?([0-9]{4})/";
    const SEPARATOR = '; ';
    const PHONE_ENTRY_FORMAT = '%s(%s)(%s)';    // Number (firstname)(H|M|W)

    public function __construct(Rider $rider)
    {
        /** @var RegistrationRcd */
        $regRcd = $rider->getRegistrationRcd();

        $this->bib = $rider->getBibNumber();
        $this->plateName = strtoupper($rider->getNickname());
        $this->fullName = sprintf("%s %s", $rider->getFirstName(), $rider->getLastName());
        $this->birthDate = $regRcd->getBirthDate()->format("Y-m-d");
        $this->raceCategory = $rider->getCategory();
        $this->gender = $rider->getGender();

        $this->team = $rider->getTeam();
        $this->grade = $rider->getGrade();

        $this->parentNames = $this->extractParentNames($regRcd);
        $this->parentPhones = $this->extractParentPhones($regRcd);
        $this->emergencyContacts = $this->extractEmergencyContacts($regRcd);

        $this->medicalInfo = $regRcd->getMedicalConditions();
        $this->allergyInfo = $regRcd->getFoodAlergies();
        $this->asthmaInfo = $regRcd->getAsthmaInfo();
        $this->ibuprofenOk = $regRcd->isIbuprofenOk() ? "Yes" : "No";
    }

    private function extractParentNames(RegistrationRcd $regRcd): string
    {
        $result = '';

        $p1Fn = $regRcd->getParent1FirstName();
        $p1Ln = $regRcd->getParent1LastName();
        $p2Fn = $regRcd->getParent2FirstName();
        $p2Ln = $regRcd->getParent2LastName();

        // If parent 2 isn't present use parent 1
        if (empty($p2Fn) && empty($p2Ln)) {
            $result = sprintf("%s %s", $p1Fn, $p1Ln);
        } else {
            // Are both last names set and the same
            if (0 == strcmp($p1Ln, $p2Ln)) {
                $result = sprintf("%s and %s %s", $p1Fn, $p2Fn, $p1Ln);
            } else {
                $result = sprintf("%s %s and %s %s", $p1Fn, $p1Ln, $p2Fn, $p2Ln);
            }
        }

        return $result;
    }

    private function extractParentPhones(RegistrationRcd $regRcd): string
    {
        $result = '';

        $p1Fn = $regRcd->getParent1FirstName();
        $p1Cn = $this->formatPhone($regRcd->getParent1CellPhone());
        $p1Hn = $this->formatPhone($regRcd->getParent1HomePhone());
        $p2Fn = $regRcd->getParent2FirstName();
        $p2Cn = $this->formatPhone($regRcd->getParent2CellPhone());
        $p2Hn = $this->formatPhone($regRcd->getParent2HomePhone());

        if (!empty($p1Cn)) {
            $result = $this->appendWithSeparator(
                $result,
                sprintf(self::PHONE_ENTRY_FORMAT, $p1Cn, $p1Fn, "M"),
                self::SEPARATOR
                );
        }

        if (!empty($p1Hn) && !empty($p1Cn) && 0 != strcmp($p1Cn, $p1Hn)) {
            $result = $this->appendWithSeparator(
                $result,
                sprintf(self::PHONE_ENTRY_FORMAT, $p1Hn, $p1Fn, "H"),
                self::SEPARATOR
                );
        }

        if (!empty($p2Cn)) {
            $result = $this->appendWithSeparator(
                $result,
                sprintf(self::PHONE_ENTRY_FORMAT, $p2Cn, $p2Fn, "M"),
                self::SEPARATOR
                );
        }

        if (!empty($p2Hn) && !empty($p2Cn) && 0 != strcmp($p2Cn, $p2Hn)) {
            $result = $this->appendWithSeparator(
                $result,
                sprintf(self::PHONE_ENTRY_FORMAT, $p2Hn, $p2Fn, "H"),
                self::SEPARATOR
                );
        }

        return $result;
    }

    private function extractEmergencyContacts(RegistrationRcd $regRcd): string
    {
        $result = '';

        $e1Fn = $regRcd->getEmergencyContact1FirstName();
        $e1Ln = $regRcd->getEmergencyContact1LastName();
        $e1Cn = $this->formatPhone($regRcd->getEmergencyContact1CellPhone());
        $e1Wn = $this->formatPhone($regRcd->getEmergencyContact1WorkPhone());

        $e2Fn = $regRcd->getEmergencyContact2FirstName();
        $e2Ln = $regRcd->getEmergencyContact2LastName();
        $e2Cn = $this->formatPhone($regRcd->getEmergencyContact2CellPhone());
        $e2Wn = $this->formatPhone($regRcd->getEmergencyContact2WorkPhone());

        $result = $this->appendWithSeparator(
            $result,
            $this->formatEmergencyContact($e1Fn, $e1Ln, $e1Cn, $e1Wn),
            self::SEPARATOR
            );
        $result = $this->appendWithSeparator(
            $result,
            $this->formatEmergencyContact($e2Fn, $e2Ln, $e2Cn, $e2Wn),
            self::SEPARATOR
            );

        return $result;
    }

    private function appendWithSeparator(string $srcStr, string $addStr, string $separator): string
    {
        $result = $addStr;

        if (!empty($addStr) && !empty($srcStr)) {
            $result = $srcStr . $separator . $addStr;
        }

        return $result;
    }

    private function formatPhone(String $phone): string
    {
        $result = '';

        if (!empty($phone)) {
            $matches = array();

            if (preg_match(self::PHONE_PATTERN, $phone, $matches)) {
                $result = sprintf("%s-%s-%s", $matches[1], $matches[2], $matches[3]);
            } else {
                $result = $phone;
            }
        }

        return $result;
    }

    private function formatEmergencyContact(string $fn, string $ln, string $cn, string $wn): string
    {
        $result = '';

        if (!empty($ln) && (!empty($cn) || !empty($wn))) {
            $result .= sprintf("%s, %s", $ln, $fn);

            if (!empty($cn)) {
                $result = $this->appendWithSeparator($result, sprintf("%s(M)", $cn), " ");
            }
            if (!empty($wn) && 0 != strcmp($cn, $wn)) {
                $result = $this->appendWithSeparator($result, sprintf("%s(W) ", $wn), " ");
            }
        }

        return $result;
    }
}
