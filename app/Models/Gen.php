<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;


class Gen extends Model
{
    use HasFactory;



    public static function to_json($recs)
    {
      $_data = "";
      foreach ($recs as $v) {
        $key = trim($v);
        if (strlen($key) < 2) {
          continue;
        }
        $_data .= "'$key' : $key,<br>";
      }
  
      return $_data;
    }

    public static function fromJsons($recs = [])
    {
        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 1) {
                continue;
            }
            if ($key == 'id') {
                $_data .= "obj.{$key} = Utils.int_parse(m['{$key}']);<br>";
            } else {
                $_data .= "obj.{$key} = Utils.to_str(m['{$key}'],'');<br>";
            }
        }
        return $_data;
    }

    public  function makeVars($tables)
    {


        $_data = "";
        $i = 0;
        $done = []; 
        foreach ($tables as $v) {
            $key = trim($v);
            if (strlen($key) < 1) {
                continue;
            }
            if (in_array($key,$done)) {
                continue;
            }
            $done[] = $key;
            $i++;
            $_data .= "<br>@HiveField({$i})<br>";
            if ($key == 'id') {
                $_data .= "int {$key} = 0;<br>";
            } else {
                $_data .= "String {$key} = \"\";<br>";
                if (str_contains($key, '_id')) { 
                    $i++;
                    $_data .= "<br>@HiveField({$i})<br>";
                    $_key = str_replace('_id', '_text', $key);
                    $_data .= "String {$_key} = \"\";<br>";
                }
            }
        }

        return $_data;
    }


    public function do_get()
    {
        $tables = Schema::getColumnListing($this->table_name);
        $generate_vars = $this->makeVars($tables);
        $fromJson = Gen::fromJsons($tables);
        $toJson = Gen::to_json($tables); 
        $x = <<<EOT
  <pre>   
  import 'RespondModel.dart';
  import '../utils/Utils.dart';
  import 'package:hive_flutter/adapters.dart';

  part '{$this->class_name}.g.dart';

  @HiveType(typeId: $this->file_id)  
  class $this->class_name {
    
    static int file_id = $this->file_id;
    static String endPoint = "{$this->end_point}";
    $generate_vars
  
    static fromJson(dynamic m) {
    $this->class_name obj = new $this->class_name();
      if (m == null) {
        return obj;
      }
      
    $fromJson
    return obj;
  }
  
    
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getLocalData({String where: "1"}) async {
        await Hive.initFlutter();
        if (!Hive.isAdapterRegistered(file_id)) {
          Hive.registerAdapter({$this->class_name}Adapter());
        }
    
        var box  =await Hive.openBox&lt;{$this->class_name}&gt;('{$this->class_name}');

        return box.values.toList().cast&lt;{$this->class_name}&gt;(); 
    }
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getItems({String where = '1'}) async {
      List&lt;$this->class_name&gt; data = await getLocalData(where: where);
      if (data.isEmpty) {
        await $this->class_name.getOnlineItems();
        data = await getLocalData(where: where);
      } else {
        data = await getLocalData(where: where);
        $this->class_name.getOnlineItems();
      }
      data.sort((a, b) => b.id.compareTo(a.id));
      return data;
    }
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getOnlineItems() async {
      List&lt;$this->class_name&gt; data = [];
  
      RespondModel resp =
          RespondModel(await Utils.http_get($this->class_name.endPoint, {}));
  
      if (resp.code != 1) {
        return [];
      }
   
      return []; 
    }
  
    save() async {
  
    }
  
    toJson() {
      return {
       $toJson
      };
    }
  
 
 
  }
  </pre>
  EOT;

        return  $x;
    }
}
