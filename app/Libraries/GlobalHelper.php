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
}
