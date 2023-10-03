<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('load_ci')) {

    function load_ci() {
        return get_instance();
    }

}

if (!function_exists('cms_input')) {
    function cms_input($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group '.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            <input type="text" name="'.$name.'" value="'.$value.'" data-validate="'.$validate.'" class="form-control validate" id="'.$name.'" placeholder="'.$placeholder.'">
                        </div>';
    
    }
}

if (!function_exists('cms_text')) {
    function cms_text($label="",$value="",$size="col-xs-12")
    {
        return      '<div class="form-group '.$size.'">
                            <label>'.$label.'</label>
                            <p>'.$value.'</p>
                        </div>';
    
    }
}

if (!function_exists('cms_date')) {
    function cms_date($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group '.$size.'"><label for="'.$name.'">'.$label.'</label><div class="input-group date">
                            
                            <input type="text" name="'.$name.'" value="'.$value.'" data-validate="'.$validate.'" class="form-control validate" id="'.$name.'" placeholder="'.$placeholder.'"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div></div>';
    
    }
}


if (!function_exists('cms_hidden')) {
    function cms_hidden($name,$value="")
    {
        return      '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'">';
    
    }
}

if (!function_exists('cms_number')) {
    function cms_number($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group '.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            <input type="number" value="'.$value.'" data-validate="'.$validate.'" name="'.$name.'" class="form-control validate" id="'.$name.'" placeholder="'.$placeholder.'">
                        </div>';
    
    }
}

if (!function_exists('cms_textarea')) {
    function cms_textarea($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group '.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            <textarea name="'.$name.'" data-validate="'.$validate.'" rows=3 class="form-control validate" id="'.$name.'" placeholder="'.$placeholder.'">'.$value.'</textarea>
                        </div>';
    
    }
}

if (!function_exists('cms_textarea_disabled')) {
    function cms_textarea_disabled($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group '.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            <textarea name="'.$name.'" data-validate="'.$validate.'" rows=3 class="form-control validate" disabled id="'.$name.'" placeholder="'.$placeholder.'">'.$value.'</textarea>
                        </div>';
    
    }
}



if (!function_exists('cms_wysiwyg')) {
    function cms_wysiwyg($name,$label,$placeholder="Enter Value",$size="col-xs-6",$value="",$validate="")
    {
        return      '<div class="form-group wisiwyg'.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            <textarea name="'.$name.'" rows=3 data-validate="'.$validate.'" class="form-control validate" id="wysiwyg" placeholder="'.$placeholder.'">'.$value.'</textarea>
                        </div>';
    
    }
}

if (!function_exists('cms_dropdown')) {
    function cms_dropdown($name,$label,$values,$size="col-xs-6",$value="")
    {
        return      '<div class="form-group '.$size.'">
                            <label>'.$label.'</label>
                            '.form_dropdown($name, $values,$value,'class="form-control"').'
                        </div>';
    
    }
}

if (!function_exists('cms_dropdown_app')) {
    function cms_dropdown_app($name,$label,$values,$size="col-xs-6",$value="",$class="")
    {
        return      '<div class="form-group '.$size.'">
                            <label for="'.$name.'">'.$label.'</label>
                            '.form_dropdown($name, $values,$value,'class="form-control '.$class.'"').'
                        </div>';
    
    }
}

if (!function_exists('cms_group_dropdown')) {
    function cms_group_dropdown($name,$label,$values,$size="col-xs-6",$target,$value="")
    {
        return      '<div class="input-group '.$size.'">
                            <label>'.$label.'</label>
                            '.form_dropdown($name, $values,$value,'class="form-control"').'
                        <div class="input-group-btn">
                        <button data-toggle="modal" href="'.$target.'" type="button" class="btn btn-primary">Add New</button>
                    </div><!-- /btn-group --></div>';
    
    }
}

if (!function_exists('cms_group_checkbox')) {
    function cms_group_checkbox($name,$label,$values,$size="col-xs-12",$target,$value=array())
    {
        $form=   '<div class="'.$size.'"><div id="'.$name.'" class="form-group"><label>'.$label.'</label>';
                foreach($values as $k=>$v)
                {
                if(!in_array($k,$value))
                    $form.=    ' <div class="checkbox">
                                <label><input value="'.$k.'" name="'.$name.'[]" type="checkbox"/> '.$v.'</label>
                                
                             </div>';
                else
                    $form.=    ' <div class="checkbox">
                                <label><input checked value="'.$k.'" name="'.$name.'[]" type="checkbox"/> '.$v.'</label>
                                
                             </div>';
                }
                $form.='</div><button data-toggle="modal" href="'.$target.'" type="button" class="btn btn-primary">Add New</button>
                   <hr style="clear:both" /> </div>';
        
        return $form;
    
    }
}

if (!function_exists('cms_group_checkbox2')) {
    function cms_group_checkbox2($name,$label,$values,$size="col-xs-12",$target,$value=array())
    {
        $form=   '<div class="'.$size.'"><label>'.$label.'</label><div id="'.$name.'" class="form-group row">';
                foreach($values as $k=>$v)
                {
                if(!in_array($k,$value))
                    $form.=    ' <div class="checkbox col-xs-4" style="margin-top:1rem;">
                                <label><input value="'.$k.'" name="'.$name.'[]" type="checkbox"/> '.$v.'</label>
                                
                             </div>';
                else
                    $form.=    ' <div class="checkbox col-xs-4" style="margin-top:1rem;">
                                <label><input checked value="'.$k.'" name="'.$name.'[]" type="checkbox"/> '.$v.'</label>
                                
                             </div>';
                }
                $form.='</div></div>';
        
        return $form;
    
    }
}

if (!function_exists('cms_group_checkbox_amount')) {
    function cms_group_checkbox_amount($name,$label,$values,$size="col-xs-12",$target,$value=array())
    {
        $form=   '<div class="'.$size.'"><label>'.$label.'</label><div id="'.$name.'" class="form-group row">';
                foreach($values as $k=>$v)
                {
                if(!in_array($k,$value))
                    $form.=    ' <div class="checkbox col-sm-4" style="margin-top:1rem;">
                                <label><input value="'.$k.'" class="cb-amount" name="'.$name.'[]" type="checkbox"/> '.$v.'&nbsp;
                                <input disabled placeholder="amount" class="amount-box" type="hidden" value="1" name=""></label>
                             </div>';
                else
                    $form.=    ' <div class="checkbox col-sm-4" style="margin-top:1rem;">
                                <label><input checked class="cb-amount" value="'.$k.'" name="'.$name.'[]" type="checkbox"/> '.$v.'&nbsp;
                                <input  placeholder="amount" class="amount-box" type="hidden" value="1" name=""></label>
                             </div>';
                }
                $form.='</div></div>';
        
        return $form;
    
    }
}



if (!function_exists('cms_submit')) {
    function cms_submit($label,$classes="btn btn-primary")
    {
        return      '<div class="form-group col-xs-12">
                        <input type="submit" value="'.$label.'" class="'.$classes.'">
                    </div>';
    
    }
}
if (!function_exists('cms_form_close')) {
    function cms_form_close()
    {
        return '<div style="clear:both"></div></form>';
    }

}

if (!function_exists('cms_form_open')) {

    function cms_form_open($id,$action,$method)
    {
        return '<form enctype="application/x-www-form-urlencoded" id="'.$id.'" action="'.$action.'" method="'.$method.'" role="form">';
    }
}

if(!function_exists('cms_image_upload'))
{
    function cms_image_upload($name,$label,$id,$table,$value="")
    {
        
        if($value=="")
            $value="default.jpg";
        
        $form='<div class="form-group col-xs-12"><input type="hidden" value="'.$value.'"  id="strPicture" name="strPicture" />
            <label>Header Image</label>
            <div class="large-12">
                <a class="th radius" href="#">
                  <img id="imageThumb" style="width:200px;" src="'.base_url().IMAGE_UPLOAD_DIR.$value.'">
                </a>
                <a data-toggle="modal" class="edit-icon btn" href="#fileUpload">
                    <i class="fa fa-pencil-square-o"></i> 
                </a>
            </div></div>';
        
        return $form;
    
    }
}

if(!function_exists('convert_number')){
function convert_number($number) {
		if (($number < 0) || ($number > 999999999)) {
			throw new Exception("Number is out of range");
		}
		$Gn = floor($number / 1000000);
		/* Millions (giga) */
		$number -= $Gn * 1000000;
		$kn = floor($number / 1000);
		/* Thousands (kilo) */
		$number -= $kn * 1000;
		$Hn = floor($number / 100);
		/* Hundreds (hecto) */
		$number -= $Hn * 100;
		$Dn = floor($number / 10);
		/* Tens (deca) */
		$n = $number % 10;
		/* Ones */
		$res = "";
		if ($Gn) {
			$res .= $this->convert_number($Gn) .  "Million";
		}
		if ($kn) {
			$res .= (empty($res) ? "" : " ") .convert_number($kn) . " Thousand";
		}
		if ($Hn) {
			$res .= (empty($res) ? "" : " ") .convert_number($Hn) . " Hundred";
		}
		$ones = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen");
		$tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", "Seventy", "Eigthy", "Ninety");
		if ($Dn || $n) {
			if (!empty($res)) {
				$res .= " and ";
			}
			if ($Dn < 2) {
				$res .= $ones[$Dn * 10 + $n];
			} else {
				$res .= $tens[$Dn];
				if ($n) {
					$res .= "-" . $ones[$n];
				}
			}
		}
		if (empty($res)) {
			$res = "zero";
		}
		return $res;
	}
}

if(!function_exists('cms_image_gallery'))
{
    function cms_image_gallery($picture,$id)
    {
        
        
        $form='<div style="background:url('.base_url().IMAGE_UPLOAD_DIR.$picture.') center center no-repeat;height:150px;background-size:90% 90%;margin-bottom:1rem;" class="col-sm-3 col-xs-4" >
                   
                    <a href="#" class="delete-gallery btn remove-icon" rel="'.$id.'"><i class="fi-pencil"></i></a>
                </div>';
        
        return $form;
    
    }
}

if (!function_exists('cms_table_generate')) {

    function cms_table_generate($id,$items,$labels,$label)
    {
        $table =  '<table id="'.$id.'" class="table table-hover">
                                        <thead><tr>';
        foreach($labels as $key=>$value){
            $table.='<th>'.$key.'</th>';
        }
            $table.='<th></th>
                                        </tr>
                                        </thead>
                                        <tbody>';
        foreach($items as $item){
            $table.='<tr>';
            foreach($labels as $key=>$value){
                
                    $table.='<td>'.$item[$value].'</td>';
            }
            
            $id = isset($item['intID'])?$item['intID']:$item['intEmpID'];
            $table .='<td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default">Actions</button>
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">';
            if($label != "contact" && $label != "borrow" && $label != "approve"){
                            $table.='<li><a href="'.base_url().'cms/edit_'.$label.'/'.pw_hash($id).'"><i class="fi-widget"></i> Edit</a></li>';
            }
        if($label == "contact"){    
          $table .= '<li><a href="'.base_url().'cms/'.$label.'_viewer/'.pw_hash($id).'"><i class="fi-results"></i> View</a></li>';
        }
        if($label == "borrow"){    
          $table .= '<li><a href="'.base_url().'cms/cancel_request/'.pw_hash($id).'" class="cancel-request"><i class="fi-trash"></i> Cancel</a></li>';
        }
        if($label == "approve"){    
          $table .= '<li><a href="'.base_url().'cms/change_request_status/'.pw_hash($id).'" class="change-request"><i class="fi-widget"></i> Change Status</a></li>';
        }
        if($label != "contact" && $label != "borrow" && $label != "approve"){
          $table .='<li> <a href="#" class="trash-'.$label.'" rel="'.pw_hash($id).'"><i class="fi-trash"></i> Delete</a></li>';
        }
         $table .= '</ul>

                    </div>
                </td>
            </tr>';
        }
                                        
                $table.=   ' </tbody></table>
                                ';
        
        return $table;
    }
}

if (!function_exists('dropdown_menu_open')) {

    function dropdown_menu_open()
    {
        return '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
    }
}

if (!function_exists('dropdown_menu_close')) {

    function dropdown_menu_close()
    {
        return '</ul></div>';
    }
}

if (!function_exists('get_enum_values')) {
    function get_enum_values( $table, $field )
    {
        $type = $this->db->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->row( 0 )->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $enum = explode("','", $matches[1]);
        return $enum;
    }
}
if (!function_exists('time_lapsed')) {

    function time_lapsed($then)
    {
        
        $then = new DateTime($then);
 
        $now = new DateTime();

        $sinceThen = $then->diff($now);
        
        if($sinceThen->y==0 && $sinceThen->m == 0 && $sinceThen->d == 0 && $sinceThen->h == 0 && $sinceThen->i == 0 && $sinceThen->s > 0 )
            return $sinceThen->s.' seconds ago';
        if($sinceThen->y==0 && $sinceThen->m == 0 && $sinceThen->d == 0 && $sinceThen->h == 0 && $sinceThen->i > 0 )
            return $sinceThen->i.' minutes ago';
        else if($sinceThen->y==0 && $sinceThen->m == 0 && $sinceThen->d == 0 && $sinceThen->h > 0)
            return $sinceThen->h.' hours ago';
        else if($sinceThen->y==0 && $sinceThen->m == 0 && $sinceThen->d > 0)
            return $sinceThen->d.' days ago';
        else if($sinceThen->y==0 && $sinceThen->m > 0)
            return $sinceThen->m.' months ago';
        else if($sinceThen->y > 0)
            return $sinceThen->y.' years ago';
        
    }

}

