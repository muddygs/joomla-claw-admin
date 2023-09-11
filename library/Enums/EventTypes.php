<?php
namespace ClawCorpLib\Enums;

enum EventTypes : int
{
    case none = 0;
    case main = 1;
    case vc = 3;
    case refunds = 4;
}
