<?php
/*******************************************************************************

    Copyright 2012 Whole Foods Co-op

    This file is part of Fannie.

    Fannie is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Fannie is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include(dirname(__FILE__) . '/../../config.php');
if (!class_exists('FannieAPI')) {
    include($FANNIE_ROOT.'classlib2.0/FannieAPI.php');
}

class TenderInOutReport extends FannieReportPage
{
    public $description = '[Tender Usages] lists each transaction for a given tender in a given date range.';
    public $report_set = 'Tenders';
    public $themed = true;

    protected $title = "Fannie : Tender Usage";
    protected $header = "Tender Usage Report";

    protected $report_headers = array('Date', 'Receipt#', 'Employee', 'Register', 'Amount');
    protected $required_fields = array('date1', 'date2');

    public function report_description_content()
    {
        $code = FormLib::get('tendercode');

        return array(
            'For tender '.$code,
        );
    }

    public function fetch_report_data()
    {
        global $FANNIE_OP_DB;
        $dbc = FannieDB::get($FANNIE_OP_DB);

        $date1 = FormLib::get('date1', date('Y-m-d'));
        $date2 = FormLib::get('date2', date('Y-m-d'));
        $code = FormLib::get('tendercode');

        $dlog = DTransactionsModel::selectDlog($date1,$date2);

        $query = $dbc->prepare_statement("select tdate,trans_num,-total as total,emp_no, register_no
              FROM $dlog as t 
              where t.trans_subtype = ? AND
              trans_type='T' AND
              tdate BETWEEN ? AND ?
              AND total <> 0
              order by tdate");
        $result = $dbc->exec_statement($query,array($code,$date1.' 00:00:00',$date2.' 23:59:59'));


        $data = array();
        while ($row = $dbc->fetch_array($result)) {
            $record = array(
                date('Y-m-d', strtotime($row['tdate'])),
                $row['trans_num'],
                $row['emp_no'],
                $row['register_no'],
                $row['total'],
            );
            $data[] = $record;
        }

        return $data;
    }

    public function calculate_footers($data)
    {
        $sum = 0.0;
        foreach($data as $row) {
            $sum += $row[4];
        }

        return array('Total', '', null, null, $sum);
    }

    public function form_content()
    {
        global $FANNIE_OP_DB;
        $dbc = FannieDB::get($FANNIE_OP_DB);
        $tenders = array();
        $p = $dbc->prepare_statement("SELECT TenderCode,TenderName FROM tenders ORDER BY TenderName");
        $r = $dbc->exec_statement($p);
        while($w = $dbc->fetch_row($r)) {
            $tenders[$w['TenderCode']] = $w['TenderName'];
        }

        ob_start();
        ?>
<form method = "get" action="TenderInOutReport.php">
<div class="col-sm-4">
    <div class="form-group"> 
        <label>Reason</label>
        <select name="tendercode" class="form-control">
            <?php foreach($tenders as $code=>$name) {
                printf('<option value="%s">%s</option>',$code,$name);
            } ?>
        </select>
    </div>
    <div class="form-group"> 
        <label>Date Start</label>
        <input type=text id=date1 name=date1 required
            class="form-control date-field" />
    </div>
    <div class="form-group"> 
        <label>Date End</label>
        <input type=text id=date2 name=date2 required
            class="form-control date-field" />
    </div>
    <div class="form-group"> 
        <input type="checkbox" name="excel" id="excel" value="xls" />
        <label for="excel">Excel</label>
    </div>
    <div class="form-group"> 
        <button type=submit name=submit value="Submit"
            class="btn btn-default">Submit</button>
        <button type=reset name=reset value="Start Over"
            class="btn btn-default">Start Over</button>
    </div>
</div>
<div class="col-sm-4">
    <?php echo FormLib::date_range_picker(); ?>
</div>
</form>
        <?php

        return ob_get_clean();
    }
}

FannieDispatch::conditionalExec();

?>
