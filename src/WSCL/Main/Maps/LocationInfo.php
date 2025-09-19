<?php
declare(strict_types = 1);
namespace WSCL\Main\Maps;

use JsonSerializable;

class LocationInfo implements \JsonSerializable
{
    private string $markerTitle;
    private string $windowTitle;
    private string $windowContent;
    private float  $latitude;
    private float  $longitude;

    public function __construct(string $markerTitle, string $windowTitle = null)
    {
        $this->markerTitle = $markerTitle;
        $this->windowTitle = is_null($windowTitle) ? $markerTitle : $windowTitle;
        $this->windowContent = '';

        $this->latitude = 0;
        $this->longitude = 0;
    }

    public function getMarkerTitle(): string
    {
        return $this->markerTitle;
    }

    public function setWindowTitle(string $title): void
    {
        $this->windowTitle = $title;
    }

    public function getWindowTitle(): string
    {
        return $this->windowTitle;
    }

    public function setWindowContent(string $content): void
    {
        $this->windowContent =
            addslashes(
                trim(
                    preg_replace("/ +/", " ",
                        preg_replace("/[\r\n\t]/", " ", $content)
                        )
                    )
            );
    }

    public function getWindowContent(): string
    {
        return $this->windowContent;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'markerTitle' => $this->markerTitle,
            'windowTitle' => $this->windowTitle,
            'windowContent' => $this->windowContent,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
            ];
    }

}
