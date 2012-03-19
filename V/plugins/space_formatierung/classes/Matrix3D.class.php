<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Matrix3D {

    static public function special_format_stargroup($text) {
        $text = preg_replace('/\[stargroup([^\]]*)\]([^~]*)\[\/stargroup\]/e', "Matrix3D::createStargroup('\\2', '\\1')", $text);
        return $text;
    }

    static public function createStargroup($json, $options) {
        $options = explode(":", $options);
        if (is_array($options)) {
            foreach ($options as $option) {
                if (is_numeric($option)) {
                    $width = $option;
                }
                if (preg_match("/zoom/", $option)) {
                    $option = str_replace("zoom", "", $option);
                    if (is_numeric($option)) {
                        $zoom = pow(0.8, $option);
                    }
                }
            }
        }
        $zoom OR ($zoom = 1);
        $width OR ($width = 500);

        $json = html_entity_decode($json, ENT_COMPAT, 'UTF-8');
        $json = "[".$json."]";
        $json = preg_replace("/([a-zA-Z0-9_]+?)\s*:/" , "\"$1\":", $json); // fix variable names
        $systems = json_decode($json, true);
        if ($systems === null) {
            return "Datensatz der Sternengruppe ist defekt.\n";
        }
        $new_systems = array();

        //viewpoint herausfinden:
        $middle = array('x' => 0, 'y' => 0, 'z' => 0);
        foreach ($systems as $key => $system) {
            isset($system['color']) OR ($system['color'] = "ffffaa");
            isset($system['size']) OR ($system['size'] = "5");

            isset($system['x']) OR ($system['x'] = 0);
            isset($system['y']) OR ($system['y'] = 0);
            isset($system['z']) OR ($system['z'] = 0);
            isset($system['connections']) OR $system['connections'] = array();

            $middle['x'] += $system['x'];
            $middle['y'] += $system['y'];
            $middle['z'] += $system['z'];
            $new_systems[$system['name']] = $system;
        }
        $middle['x'] = $middle['x'] / count($systems);
        $middle['y'] = $middle['y'] / count($systems);
        $middle['z'] = $middle['z'] / count($systems);
        $mindiff = 5.1;
        ($middle['x'] > $mindiff OR $middle['x'] < -$mindiff) OR ($middle['x'] = 0);
        ($middle['y'] > $mindiff OR $middle['y'] < -$mindiff) OR ($middle['y'] = 0);
        ($middle['z'] > $mindiff OR $middle['z'] < -$mindiff) OR ($middle['z'] = 0);

        $maximum = 10;
        foreach ($systems as $system) {
            if ($maximum < abs($system['x'] - $middle['x'])) {
                $maximum = abs($system['x'] - $middle['x']);
            }
            if ($maximum < abs($system['y'] - $middle['y'])) {
                $maximum = abs($system['y'] - $middle['y']);
            }
            if ($maximum < abs($system['z'] - $middle['z'])) {
                $maximum = abs($system['z'] - $middle['z']);
            }
        }

        $output = Template::summon(dirname(__file__)."/../views/stargroup.php")
                        ->with("systems", $new_systems)
                        ->with("viewcenter", $middle)
                        ->with("width", $width)
                        ->with("zoom", $zoom)
                        ->with("viewradius", $maximum)
                        ->render();
        //print $output;
        return preg_replace("/\s+/", " ", $output);
    }

}
