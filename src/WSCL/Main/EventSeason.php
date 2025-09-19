<?php
declare(strict_types = 1);
namespace WSCL\Main;

enum EventSeason: string {
    case CURRENT = "CURRENT";
    case LAST = "LAST";
    case PREVIOUS = "PREVIOUS";
    case EARLIER = "EARLIER";
    case FUTURE = "FUTURE";
}
