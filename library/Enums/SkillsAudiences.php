<?php
namespace ClawCorpLib\Enums;

enum SkillsAudiences: string {
  case Open = "Open";
  case Men_Only = "Men Only";
  case Private = "Private";
  case Reg_Required = "Reg Required";
  case Gear = "Gear Required";
}