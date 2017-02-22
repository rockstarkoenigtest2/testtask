<?php

class VoltExtensions
{

    public static function joinGroupNames($groupsData) {
        $a = [];
        foreach ($groupsData as $item) {
            $a[] = $item->name;
        }
        return implode(", ", $a);
    }

}