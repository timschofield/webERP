<?php
define('MAX_LINES_PER_LABEL', 5);
define('LABELS_FILE', $_SESSION['reports_dir'] . '/labels.xml');

/**
 *  These tags contains the more general data of the labels
 */
$GlobalTags = array('id'=>array('desc'=> _('Label id'),
							'type'=>'t',
							'sz'=>8,
							'maxsz'=>12),  // text
							'description'=>array('desc'=>_('Description'),
							'type'=>'t',
							'sz'=>15,
							'maxsz'=>30)  // text
					);
/**
 *  These tags specifies the dimension of individual label
 */
$DimensionTags = array(
    'Unit'=>array('desc'=>_('Units'),'type'=>'s',
    'values'=>array('pt'=>'pt', 'in'=>'in', 'mm'=>'mm', 'cm'=>'cm' ) ), // select
    'Rows'=>array('desc'=>_('Rows per sheet'),'type'=>'i','sz'=>2,'maxsz'=>3), // integer numeric
    'Cols'=>array('desc'=>_('Cols per sheet'),'type'=>'i','sz'=>2,'maxsz'=>3),
    'Sh'=>array('desc'=>_('Sheet height'),'type'=>'n','sz'=>5,'maxsz'=>8),  // float numeric
    'Sw'=> array('desc'=>_('Sheet width'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'He'=>array('desc'=>_('Label height'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'Wi'=> array('desc'=>_('Label width'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'Tm'=>array('desc'=>_('Top margin'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'Lm'=> array('desc'=>_('Left margin'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'Rh'=> array('desc'=>_('Row height'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'Cw'=>array('desc'=>_('Column width'),'type'=>'n','sz'=>5,'maxsz'=>8)
);
/**
 *  These tags will come as arrays from the input screen, ie: row[], pos[], etc.
 */
$DataTags =  array(
    'row'=>array('desc'=>_('Vert. pos.'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'pos'=>array('desc'=>_('Horiz. pos.'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'max'=> array('desc'=>_('Max text<br />length'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'font'=>array('desc'=>_('Font size'),'type'=>'n','sz'=>5,'maxsz'=>8),
    'dat'=>array('desc'=>_('Data to display'),'type'=>'s',
        'values'=>array(
            'code'=>_('Code item'),
            'name1'=>_('Description'),
            'name2'=>_('Description remainder'),
            'lname1'=>_('Long description'),
            'lname2'=>_('Long description remainder'),
            'price'=>_('Price'),
            'bcode'=>_('Bar Code') ) )
);

/*! \brief Read the list of labels from the XML file
 *
 *  This routine uses of the SimpleXML library to read the labels
 *  defined in the file of labels.
 *
 *  @param  $file is the name of the XML file
 *  @return the list of label in an objects array
 */
function getXMLFile($file) {
    $list=null;
    libxml_use_internal_errors(true);

    if (file_exists($file)) {
        $list= simplexml_load_file($file, "LabelList");
        if (!$list) {
            prnMsg(_('Failed loading XML file').' '. $file.':');
            foreach(libxml_get_errors() as $error) {
                echo "
                <br />", $error->message;
            }
            exit(_('Report this problem'));
        }
    }
    return $list;
}

/**
 *  The newLabel function.
 */
function newLabel($data=null) {
    global $GlobalTags, $DimensionTags, $DataTags;
    $label0 = '<?xml version="1.0"  standalone="yes"?><label><id>""</id><description>""</description><dimensions></dimensions><data></data></label>';
    $label = new LabelList($label0);
    if ($data!=null) {
        foreach ($GlobalTags as $iTag=>$tag)
            $label->$iTag = $data[$iTag];
        foreach ($DimensionTags as $iTag=>$tag)
            $label->dimensions->addChild($iTag, $data[$iTag]);
    // we suppose that this data comes from the input, then there are:
    // $data[$tag][$i] begining with $data['row'][$i]
        foreach ($data['row'] as $i=>$val) {
            if (empty($val)) continue;
            $line=$label->data->addChild('line');
            foreach ($DataTags as $iTag=>$tag)
                $line->addChild($iTag, $data[$iTag][$i]);
        }
    }
    return $label;
}

function emptyList() {
    $emptyListXML = '<?xml version="1.0"?><labels></labels>';
    return new LabelList($emptyListXML);
}


/**
 *  The ListLabel class.
 *
 *  The composition of label object is supossed to be set up by three parts:
 *  - globals:    id and description of the label
 *  - dimensions: physical specification of the label
 *  - data
 *
 *  The last one is an ocurrence of an array with 5 elements maximum, describing
 *  the field characteristic to be included in the printed label
 */

class LabelList extends SimpleXMLElement {
    function getLabel($labelID) {
        foreach ($this->label as $label) {
            if ($label->id == $labelID)
                return $label;
        }
        return false;
    }

    function findLabel($labelID) {
        foreach ($this->label as $i=>$ll) {
            if ((string)$ll->id==$labelID)
                return $i;  // found!
        }
        return false; // Not found!
    }

    function addLabel($label) {
        global $GlobalTags, $DimensionTags, $DataTags;
        $new=$this->addChild('label');
        foreach ($GlobalTags as $iTag=>$tag)
            $new->addChild($iTag, (string)$label->$iTag);
        $dimensions=$new->addChild('dimensions');
        foreach ($DimensionTags as $iTag=>$tag)
            $dimensions->addChild($iTag, (string)$label->dimensions->$iTag);
    // $data[$tag][$i] begining with $data['row'][$i]
        $data=$new->addChild('data');
        foreach ($label->data->line as $line) {
            $newLine=$data->addChild('line');
            foreach ($DataTags as $iTag=>$tag)
                $newLine->addChild($iTag, (string)$line->$iTag);
        }
        return true;
    }
}

/*! \brief Abort routine
 *
 *  This routine print an error message passed as parameter and
 *  finishes the program execution. It shows the footer part of
 *  the general screen.
 *
 *  @param  $msg is the error message
 *  @return nothing
 */
function abortMsg($msg) {
    global $RootPath, $DefaultClock, $Version, $Theme;
    $Title=_('No label templates exist');
    include ('includes/header.php');
    echo '<br />';
    prnMsg( $msg, 'error');
    include ('includes/footer.php');
    exit;
}
?>