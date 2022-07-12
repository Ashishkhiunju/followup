<?php
use App\Helper\Nepali_Calendar;

    function eng_to_nep_num_convert($number){
        $nep_number = [
            "0"=> "०",
             "1"=>"१",
             "2"=>"२",
             "3"=>"३",
             "4"=>"४",
             "5"=>"५",
             "6"=>"६",
             "7"=>"७",
             "8"=>"८",
             "9"=>"९"
         ];
         $eng_number = [
            "0"=> "0",
             "1"=>"1",
             "2"=>"2",
             "3"=>"3",
             "4"=>"4",
             "5"=>"5",
             "6"=>"6",
             "7"=>"7",
             "8"=>"8",
             "9"=>"9"
         ];
        return str_replace($eng_number, $nep_number, $number);
    }

    function nep_to_eng_num_convert($number){

        $nep_number = [
           "0"=> "०",
            "1"=>"१",
            "2"=>"२",
            "3"=>"३",
            "4"=>"४",
            "5"=>"५",
            "6"=>"६",
            "7"=>"७",
            "8"=>"८",
            "9"=>"९"
        ];
        $eng_number = [
           "0"=> "0",
            "1"=>"1",
            "2"=>"2",
            "3"=>"3",
            "4"=>"4",
            "5"=>"5",
            "6"=>"6",
            "7"=>"7",
            "8"=>"8",
            "9"=>"9"
        ];
        return str_replace($nep_number,$eng_number, $number);
    }

    function eng_date($date){
        $explodeyear = explode('-',$date);
        $year = nep_to_eng_num_convert($explodeyear[0]);
        $month = nep_to_eng_num_convert($explodeyear[1]);
        $day = nep_to_eng_num_convert($explodeyear[2]);
        $cal = new Nepali_Calendar();
        $engdate = $cal->nep_to_eng($year,$month,$day);
        return $engdate['year'].'-'.$engdate['month'].'-'.$engdate['date'];
    }

    function nep_date($date){

        $explodeyear = explode('-',$date);
        $year = $explodeyear[0];
        $month = $explodeyear[1];
        $day = $explodeyear[2];
        $explodedate = explode(' ',$day);
        $date = $explodedate[0];

        $cal = new Nepali_Calendar();
        $nepdate = $cal->eng_to_nep($year,$month,$date);

        return eng_to_nep_num_convert($nepdate['year']).'-'.eng_to_nep_num_convert($nepdate['month']).'-'.eng_to_nep_num_convert($nepdate['date']);
    }

?>
