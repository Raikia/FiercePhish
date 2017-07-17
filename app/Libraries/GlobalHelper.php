<?php

namespace App\Libraries;

class GlobalHelper
{
    public static function makeUnclickableLink($str)
    {
        return str_replace(['http://', 'https://', '.'], ['hxxp://', 'hxxps://', '[.]'], $str);
    }
    
    public static function generateGraphData($queryable, $datetype = 'created_at')
    {
        $rawData = $queryable->orderby($datetype, 'asc')->get();
        $graphData = [];
        if (count($rawData) > 0) {
            $dayIterator = DateHelper::convert($rawData[0]->$datetype);
            $today = DateHelper::now();
            $graphData = [];
            $dataCounter = 0;
            while ($dayIterator->lt($today)) {
                $count = 0;
                while ($dataCounter < $rawData->count() && DateHelper::convert($rawData[$dataCounter]->$datetype)->isSameDay($dayIterator)) {
                    $count += 1;
                    $dataCounter += 1;
                }
                $graphData[] = [$dayIterator->format('Y-m-d'), $count];
                $dayIterator = $dayIterator->addDay(1);
            }
        }
        
        return $graphData;
    }
    
    public static function isLowerVersion($current_version, $latest_version)
    {
        $cv = explode('.', $current_version);
        $lv = explode('.', $latest_version);
        $cv = array_pad($cv, count($lv), 0);
        $lv = array_pad($lv, count($cv), 0);
        for ($x = 0; $x < count($cv); ++$x) {
            if ($cv[$x] < $lv[$x]) {
                return true;
            }
        }
        
        return false;
    }
}
