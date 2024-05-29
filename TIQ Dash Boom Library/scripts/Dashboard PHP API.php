<?php

use Joomla\CMS\Response\JsonResponse;
use TiqUtilities\Model\Node;
use TiqUtilities\Model\Type;
use TiqUtilities\Model\GenericObject;


require_once 'thinkiq_context.php';
$context = new Context();

$f = isset($context->std_inputs->function) ? $context->std_inputs->function : '';
$a = isset($context->std_inputs->argument) ? json_decode($context->std_inputs->argument) : '';

function PostgresArrayToPhpArray($postgresArray){
    return (fn () => $this->convertPostgresTypeArray($postgresArray))->call(new Node()); 
}


switch ($f){

    case "Echo":

        $returnObject = $a->hello == null ? "Hello Echo." : $a->hello;
        die(new JsonResponse($returnObject));

    case "LoadDashboard":

    // function Panel(aType, aTypeId, aLabel, aConfig, aW, aH){
    // x    this.type = aType;
    // x    this.typeFqn = PostgresArrayToPhpArray(aType->fqn)
    // x    this.config=aConfig;
    // x    this.i= aLabel.replaceAll(' ', '') + '_' + crypto.randomUUID().split('-')[0];
    // x    this.x= 30;
    // x    this. y= 0;
    // x    this.w= aW;
    // x    this.wPx= 0;
    // x    this.h= aH;
    // x    this.hPx= 0;
    // x    this.moved= false;
    // }


        $aId = $a->id;
        $aHost = new Node($aId);
        $aHost->getChildren();

        $aLayout = [];
        foreach($aHost->children as $aWidget){
            $aWidget->getAttributes();
            $aGridItem = [];

            // identifier and typeId of the widget
            $aGridItem['i'] = $aWidget->display_name;
            $aGridItem['typeFqn'] = explode('.', (new Node($aWidget->type_id))->getFqn());

            // lookup the type to get the type id
            $widgetType = new Type($aWidget->type_id);
            $widgetType->getAttributes();
            $aGridItem['componentName'] = $widgetType->attributes['component_name']->default_string_value;

            // position and size of widget
            $intProps = ['x', 'y', 'w', 'h'];
            foreach($intProps as $aProp){
                $aGridItem[$aProp] = (int)$aWidget->attributes[$aProp]->int_value;
            }

            // those are required by vue-grid-layout
            $aGridItem['wPx'] = 0;
            $aGridItem['hPx'] = 0;
            $aGridItem['moved'] = false;

            // config props
            $aConfig = [];
            foreach($aWidget->attributes as $aAttribute){
                if(str_starts_with($aAttribute->display_name, 'config:')){
                    $stringBits = explode(':', $aAttribute->display_name);

                    switch($aAttribute->data_type){
                        case 'string':
                            $aConfig[$stringBits[1]] = $aAttribute->string_value; 
                            break;
                        case 'int':
                            $aConfig[$stringBits[1]] = $aAttribute->int_value; 
                            break;
                        case 'float':
                            $aConfig[$stringBits[1]] = $aAttribute->float_value; 
                            break;
                    }
                }
            }
            $aGridItem['config'] = (object)$aConfig;


            $aLayout[] = (object)$aGridItem;
        }

        $aResponseObject = [];
        $aResponseObject['layout'] = $aLayout;

        $aHost->getAttributes();
        $aResponseObject['pageTitle'] = $aHost->attributes['page_title']->string_value;
        $aResponseObject['tabTitle'] = $aHost->attributes['tab_title']->string_value;

        die(new JsonResponse((object)$aResponseObject));


    case "SaveDashboard":

        $aId = $a->id;
        $aLayout = $a->layout;
        $aPageTitle = $a->pageTitle;
        $aTabTitle = $a->tabTitle;

        $aHost = new Node($aId);

        // save page and tab title
        $aHost->getAttributes();
        $aHost->attributes['page_title']->string_value = $aPageTitle;
        $aHost->attributes['page_title']->save();
        $aHost->attributes['tab_title']->string_value = $aTabTitle;
        $aHost->attributes['tab_title']->save();


        $aHost->getChildren();
        // get a dictionary of existing widgets key: relative name, value: display_name
        $existingWidgets = array_map(function($aItem){return $aItem->display_name;}, $aHost->children);

        foreach($aLayout as $aGridItem){
            


            // create instance of widget type if needed
            if(!in_array($aGridItem->i, $existingWidgets)){
                $aWidget = new GenericObject();
                $aWidget->display_name = $aGridItem->i;
                $aWidget->type_id = (new Type(implode('.', $aGridItem->typeFqn)))->id;
                $aWidget->part_of_id=$aHost->id;
                $aWidget->save();
            } else {
                $aWidget = new GenericObject($aHost->children[array_search($aGridItem->i, $existingWidgets)]->id);
            }

            // populate attributes
            $aWidget->getAttributes();

            // position and size of widget
            $intProps = ['x', 'y', 'w', 'h'];
            foreach($intProps as $aProp){
                $aWidget->attributes[$aProp]->int_value = $aGridItem->$aProp; 
                $aWidget->attributes[$aProp]->save();
            }

            // config props
            foreach($aWidget->attributes as $aAttribute){
                if(str_starts_with($aAttribute->display_name, 'config:')){
                    $stringBits = explode(':', $aAttribute->display_name);

                    switch($aAttribute->data_type){
                        case 'string':
                            $aAttribute->string_value = $aGridItem->config->{$stringBits[1]}; 
                            break;
                        case 'int':
                            $aAttribute->int_value = $aGridItem->config->{$stringBits[1]}; 
                            break;
                        case 'float':
                            $aAttribute->float_value = (float)$aGridItem->config->{$stringBits[1]}; 
                            break;
                    }
                    $aAttribute->save();
                }
            }
            
            
        }

        // remove widgets that aren't in the dashboard
        $aHost->getChildren();
        $gridItemNames = array_map(function($aGridItem){return $aGridItem->i;}, $aLayout);
        foreach($aHost->children as $aWidget){
            if(!in_array($aWidget->display_name , $gridItemNames)){
                $aWidget->delete();
            }
        }

        $returnObject = "Dashboard saved.";
        die(new JsonResponse($returnObject));
        
    default:

        $returnObject = "This function does not exist.";
        die(new JsonResponse($returnObject));

}

?>