<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

trait RefNoGenerator
{
    /**
     * Default generator ref no PREFIX+XXXX
     *
     * @param string $table
     * @param integer $digitLength
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    protected function generateRefNo(string $table, int $digitLength = 4, ?string $prefix = null, ?string $postfix = null)
    {
        // $digitLength = 4;
        $refNo = null;
        // pattern for SQL where LIKE
        $pattern = sprintf('%s' . str_repeat('_', $digitLength) . '%s', $prefix, $postfix); // $pattern will be like '____'

        $index = 1;
        $row = DB::table($table)
                ->orderBy('ref_no', 'desc')
                ->where('ref_no', 'like', $pattern)
                ->first();

        // Loop until get one unique ref no
        $refNo = null;
        while(!empty($row)) {
            // Increase XXXXX(index) by +1
            $formatted = str_replace($prefix, '', str_replace($postfix, '', $row->ref_no)); // remove prefix and postfix
            $index = (int) $formatted; // convert to integer
            $index++;

            $refNo = sprintf("%s%s%s", $prefix, sprintf('%0' . $digitLength . 'd', $index), $postfix); // add prefix and postfix with zero padding

            // Verify that ref no is unique
            $row = DB::table($table)->where('ref_no', $refNo)->first();
        };

        // When ref no is empty then it means this date doesn't have any
        // ref no with YYMM-XXXXX format
        if(empty($refNo))
            $refNo = sprintf("%s%s%s", $prefix, sprintf('%0' . $digitLength . 'd', $index), $postfix);

        return $refNo;
    }

    protected function getPrefix()
    {
        return 'PREFIX';
    }

    protected function getPostfix()
    {
        return sprintf('/%s.%s', date('m'), date('y'));
    }
}
