<?php

function format_report_numeric($value){
	$positive = true;
	if($value < 0){ 
		$positive = false;
		$value = abs($value);
	}
	$result = number_format($value, 0, ',', '.');
	if(!$positive) $result = "(".$result.")";
	return $result; 
}