<?php
declare(strict_types = 1);
namespace WSCL\Main\RaceResult\Entity;

use WSCL\Main\EventSeason;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RCS\Json\JsonDateTime;

#[CoversClass(\WSCL\Main\RaceResult\Entity\Event::class)]
class EventTest extends TestCase
{
    public function testGetId()
    {
        $testEventId = 957156;

        $event = new Event();
        $event->id = $testEventId;

        $this->assertEquals($testEventId, $event->getId());
    }

    public function testGetName()
    {
        $testEventName = 'WSCL - Port Gamble Scramble';

        $event = new Event();
        $event->name = $testEventName;

        $this->assertEquals($testEventName, $event->getName());
    }

    public function testGetDate()
    {
        $testEventDate = (new JsonDateTime())->add(new \DateInterval('P2D'));

        $event = new Event();
        $event->date = $testEventDate;

        $this->assertEquals($testEventDate, $event->getDate());
    }

    /**
     * Test isSpringRace() using a series of dates.
     */
    #[DataProvider('getSpringRaces')]
    public function testIsSpringRace(string $eventDate, bool $expected)
    {
        $event = new Event();
        $event->date = new JsonDateTime($eventDate);
        $this->assertEquals($expected, $event->isSpringRace());
    }

    public static function getSpringRaces(): array
    {
        return array(
            array('2023-05-21', true),
            array('2023-09-21', false),
        );
    }

    /**
     * Test isFallRace() using a series of dates.
     */
    #[DataProvider('getFallRaces')]
    public function testIsFallRace(string $eventDate, bool $expected)
    {
        $event = new Event();
        $event->date = new JsonDateTime($eventDate);
        $this->assertEquals($expected, $event->isFallRace());
    }

    public static function getFallRaces(): array
    {
        return array(
            array('2023-10-16', true),
            array('2023-04-30', false),
        );
    }

    /**
     * Test getEventSeason() using a series of dates.
     */
    #[DataProvider('getEventSeasons')]
    public function testGetEventSeason(string $currentDate, string $eventDate, EventSeason $eventSeason)
    {
        $event = $this->getMockBuilder(Event::class)
            ->onlyMethods(array('getCurrentDateTime'))
            ->getMock();

        $event->date = new JsonDateTime($eventDate);

        $this->assertEquals($eventSeason, $event->getEventSeason(new \DateTime($currentDate)));
    }

    public static function getEventSeasons(): array
    {
        return array(
            array('2023-10-01', '2023-10-17', EventSeason::CURRENT),  // Fall date, fall race
            array('2023-04-01', '2023-05-17', EventSeason::CURRENT),  // Spring date, spring race
            array('2023-09-15', '2023-05-17', EventSeason::LAST),     // Fall date, spring race
            array('2023-09-01', '2022-10-17', EventSeason::PREVIOUS), // Fall date, fall race (last year)
            array('2023-04-01', '2022-10-17', EventSeason::LAST),     // Spring date, fall race (last year)
            array('2023-03-31', '2022-05-17', EventSeason::PREVIOUS), // Spring date, spring race (last year)
            array('2023-02-15', '2021-05-17', EventSeason::EARLIER)   // Spring date, spring race (two years ago)
        );
    }
}
