<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 24.10.16
 * Time: 13:57
 */

namespace Concrete\Package\BaclucAccountingPackage\Src\EntityViews;


use Concrete\Package\BasicTablePackage\Src\AbstractFormView;

class MoveLineFormView extends AbstractFormView
{
    /**
     * @param $form
     * @param bool $clientSideValidationActivated
     * @return mixed
     */
    public function getFormView($form, $clientSideValidationActivated = true)
    {
        $variables = $this->getFilledVariables($clientSideValidationActivated);
        $html = "
    <div class='row'>
            ".$variables['id']['input'] ."
            <div class='row'>
                                        <div class = 'col-xs-12'>
                                            <div class='row'>
                                                <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['debit']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['debit']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>
                                                 <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['credit']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['credit']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>
                                                 <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['balance']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['balance']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>       
                                                 <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['reconciled']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['reconciled']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>
                                                 <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['date_posted']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['date_posted']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>  
                                                 <div class='col-xs-12 col-md-2'>
                                                    <div class='row'>
                                                        <div class='col-xs-12'>
                                                            <label>" . $variables['Account']['label'] . "</label>
                                                        </div>
                                                        <div class='col-xs-12'>
                                                            " . $variables['Account']['input'] . "
                                                        </div>
                                                     </div>   
                                                 </div>        
                                                       
                                            </div>
                                        </div>
                                        
              </div>
            
            
     </div>       
            ";
        return $html;
    }

}