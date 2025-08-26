<?php

namespace App\Helper;
use Request;
use Config;

use App\Models\RulesConditions;

class ConditionSetup_helper
{
    static function CreateConditionsetup($data,$ruleid, $moduletype){
        foreach ($data['conditiondata'] as $keyy => $valuee) {

                 $conditiondata = [
                    'rule_id' => $ruleid,
                    'module_type' => $moduletype,
                ];
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '01'){
                      $conditiondata['condition_type'] = 1;
                      $conditiondata['brand_id'] = implode(',', array_column($valuee['brand'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '02'){
                      $conditiondata['condition_type'] = 2;
                      $conditiondata['product_id'] = implode(',', array_column($valuee['product'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '03'){
                      $conditiondata['condition_type'] = 3;
                      $conditiondata['sub_tag_id'] = implode(',', array_column($valuee['tag'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '04'){
                      $conditiondata['condition_type'] = 4;
                      $conditiondata['category_id'] = implode(',', array_column($valuee['category'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '05'){
                      $conditiondata['condition_type'] = 5;
                      $conditiondata['payment_method_id'] = implode(',', array_column($valuee['paymentMethods'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                }
                
                // if(isset($valuee['type']['value']) && $valuee['type']['value'] == '07'){
                //       $conditiondata['condition_type'] = 7;
                //       $conditiondata['min_amount'] = isset($valuee['min']) ? $valuee['min'] : null;
                //       $conditiondata['max_amount'] = isset($valuee['max']) ? $valuee['max'] : null;
                // }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '08' || $valuee['type']['value'] == '07' || $valuee['type']['value'] == '01' || $valuee['type']['value'] == '02' || $valuee['type']['value'] == '03' || $valuee['type']['value'] == '04'){
                      $conditiondata['condition_type'] = $valuee['type']['value'];
                      $conditiondata['min_amount'] = isset($valuee['min']) ? $valuee['min'] : null;
                      $conditiondata['max_amount'] = isset($valuee['max']) ? $valuee['max'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '09'){
                      $conditiondata['condition_type'] = 9;
                      $conditiondata['date'] = isset($valuee['date']) ? $valuee['date'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '10'){
                      $conditiondata['condition_type'] = 10;
                      $conditiondata['start_time'] = isset($valuee['starttime']) ? $valuee['starttime'] : null;
                      $conditiondata['end_time'] = isset($valuee['endtime']) ? $valuee['endtime'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '11'){
                      $conditiondata['condition_type'] = 11;
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['email'] = isset($valuee['email']) ? $valuee['email'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '12'){
                      $conditiondata['condition_type'] = 12;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '13'){
                      $conditiondata['condition_type'] = 13;
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['phone_number'] = isset($valuee['phone']) ? $valuee['phone'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '14'){
                      $conditiondata['condition_type'] = 14;
                      $conditiondata['dob'] = isset($valuee['dob']) ? $valuee['dob'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '15'){
                      $conditiondata['condition_type'] = 15;
                      $conditiondata['no_of_orders'] = isset($valuee['order']) ? $valuee['order'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '16'){
                      $conditiondata['condition_type'] = 16;
                      $conditiondata['city_id'] = implode(',', array_column($valuee['city'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                }
                RulesConditions::create($conditiondata);
            }
    }
    
    static function UpdateConditionsetup($data,$ruleid, $moduletype){
        $con_data = RulesConditions::where('rule_id', '=',$ruleid)->get();
        $con_data->each->delete();
            
            foreach ($data['conditiondata'] as $keyy => $valuee) {
                
                 $conditiondata = [
                    'rule_id' => $ruleid,
                    'module_type' => $moduletype,
                ];
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '01'){
                      $conditiondata['condition_type'] = 1;
                      $conditiondata['brand_id'] = implode(',', array_column($valuee['brand'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '02'){
                      $conditiondata['condition_type'] = 2;
                      $conditiondata['product_id'] = implode(',', array_column($valuee['product'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '03'){
                      $conditiondata['condition_type'] = 3;
                      $conditiondata['sub_tag_id'] = implode(',', array_column($valuee['tag'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '04'){
                      $conditiondata['condition_type'] = 4;
                      $conditiondata['category_id'] = implode(',', array_column($valuee['category'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['select_quantity'] = isset($valuee['qtytype']['value']) ? $valuee['qtytype']['value'] : null;
                      $conditiondata['quantity'] = isset($valuee['qty']) ? $valuee['qty'] : 0;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '05'){
                      $conditiondata['condition_type'] = 5;
                      $conditiondata['payment_method_id'] = implode(',', array_column($valuee['paymentMethods'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                }
                
                // if(isset($valuee['type']['value']) && $valuee['type']['value'] == '07'){
                //       $conditiondata['condition_type'] = 7;
                //       $conditiondata['min_amount'] = isset($valuee['min']) ? $valuee['min'] : null;
                //       $conditiondata['max_amount'] = isset($valuee['max']) ? $valuee['max'] : null;
                // }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '08' || $valuee['type']['value'] == '07' || $valuee['type']['value'] == '01' || $valuee['type']['value'] == '02' || $valuee['type']['value'] == '03' || $valuee['type']['value'] == '04'){
                      $conditiondata['condition_type'] = $valuee['type']['value'];
                      $conditiondata['min_amount'] = isset($valuee['min']) ? $valuee['min'] : null;
                      $conditiondata['max_amount'] = isset($valuee['max']) ? $valuee['max'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '09'){
                      $conditiondata['condition_type'] = 9;
                      $conditiondata['date'] = isset($valuee['date']) ? $valuee['date'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '10'){
                      $conditiondata['condition_type'] = 10;
                      $conditiondata['start_time'] = isset($valuee['starttime']) ? $valuee['starttime'] : null;
                      $conditiondata['end_time'] = isset($valuee['endtime']) ? $valuee['endtime'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '11'){
                      $conditiondata['condition_type'] = 11;
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['email'] = isset($valuee['email']) ? $valuee['email'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '12'){
                      $conditiondata['condition_type'] = 12;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '13'){
                      $conditiondata['condition_type'] = 13;
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                      $conditiondata['phone_number'] = isset($valuee['phone']) ? $valuee['phone'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '14'){
                      $conditiondata['condition_type'] = 14;
                      $conditiondata['dob'] = isset($valuee['dob']) ? $valuee['dob'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '15'){
                      $conditiondata['condition_type'] = 15;
                      $conditiondata['no_of_orders'] = isset($valuee['order']) ? $valuee['order'] : null;
                }
                
                if(isset($valuee['type']['value']) && $valuee['type']['value'] == '16'){
                      $conditiondata['condition_type'] = 16;
                      $conditiondata['city_id'] = implode(',', array_column($valuee['city'], 'value'));
                      $conditiondata['select_include_exclude'] = isset($valuee['list']['value']) ? $valuee['list']['value'] : 0;
                }
                RulesConditions::create($conditiondata);
            }
        
    }
    
    static function sms($number, $message)
    {
        $username = Config::get('sms.sentsms.username');
        $password = Config::get('sms.sentsms.password');
        $senderid = Config::get('sms.sentsms.senderid');
        $url = Config::get('sms.sentsms.url');
        // print_r($url);die();
        $request  = 'user=' .$username;     
        $request  .= '&pwd=' .$password;
        if($senderid){
            $request  .= '&senderid=' .$senderid;
        }
        $request  .= '&mobileno=' .$number;
        $request  .= '&msgtext=' .urlencode($message);
        $request  .= '&priority=High&CountryCode=ALL';
        $url = $url .'?' .$request;
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($cURLConnection);
        curl_close($cURLConnection);
    }
}