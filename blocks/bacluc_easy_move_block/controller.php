<?php
namespace Concrete\Package\BaclucAccountingPackage\Block\BaclucEasyMoveBlock;

use Concrete\Core\Package\Package;
use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BaclucAccountingPackage\Src\MoveLine;
use Concrete\Package\BaclucEventPackage\Src\Event;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\DropdownBlockOption;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption;
use Concrete\Core\Block\BlockController;
use Concrete\Package\BasicTablePackage\Src\BasicTableInstance;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TextBlockOption;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FloatField;
use Core;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\CanEditOption;
use Doctrine\DBAL\Schema\Table;
use OAuth\Common\Exception\Exception;
use Page;
use User;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\Field as Field;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\SelfSaveInterface as SelfSaveInterface;
use Loader;
use Concrete\Package\BaclucAccountingPackage\Src\Move;

use Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Test as Test;

class Controller extends \Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Controller
{
    protected $btHandle = 'bacluc_easy_move_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucEasyMoveBlock";

    /**
     * Array of \Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * @var \Concrete\Package\BasicTablePackage\Src\BaseEntity
     */
    protected $model;


    /**
     * set blocktypeset
     * @var string
     */
    protected $btDefaultSet = 'bacluc_accounting_set';

    /**
     *
     * Controller constructor.
     * @param null $obj
     */
    function __construct($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new Move();
        parent::__construct($obj);



        if ($obj instanceof Block) {
         $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')->findOneBy(array('bID' => $obj->getBlockID()));

            $this->basicTableInstance = $bt;
        }


/*
 * add blockoptions here if you wish
        $this->requiredOptions = array(
            new TextBlockOption(),
            new DropdownBlockOption(),
            new CanEditOption()
        );

        $this->requiredOptions[0]->set('optionName', "Test");
        $this->requiredOptions[1]->set('optionName', "TestDropDown");
        $this->requiredOptions[1]->setPossibleValues(array(
            "test",
            "test2"
        ));

        $this->requiredOptions[2]->set('optionName', "testlink");
*/


    }



    /**
     * @return string
     */
    public function getBlockTypeDescription()
    {
        return t("Create Moves more easy");
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return t("Bacluc Easy Move");
    }



    /**
     * @return array of Application\Block\BasicTableBlock\Field
     */
    public function getFields()
    {
        if ($this->editKey == null) {
            $fields =  $this->model->getFieldTypes();
        }else{
            $fields =  $this->getEntityManager()->getRepository(get_class($this->model))->findOneBy(array($this->model->getIdFieldName() => $this->editKey))->getFieldTypes();
        }
        //set all fields to not display in form view
        foreach($fields as $sqlFieldName => &$FieldType){
            /**
             * @var Field $FieldType
             */
            $FieldType->setShowInForm(false);
        }

        //add the fields which should be displayed in form view
        $fields['formName']=new Field("formName", "Name", "formName");
        $fields['formName']->setShowInTable(false);

        $fields['formReference']=new Field("formReference", "Reference", "formReference");
        $fields['formReference']->setShowInTable(false);

        $fields['formStatus']=new DropdownField("formStatus", "Status", "formStatus");
        $fields['formStatus']->setShowInTable(false);
        $fields['formStatus']->setOptions($fields['status']->getOptions());

        $fields['formDate'] = new DateField("formDate", "Date", "formDate");
        $fields['formDate']->setShowInTable(false);

        $fields['formFromAccount']= new DropdownLinkField("formFromAccount", "From Account", "formFromAccount");
        $fields['formFromAccount']->setShowInTable(false);
        $fields['formFromAccount']->setLinkInfo(get_class(new MoveLine()), "Account",get_class(new Account()));

        $fields['formToAccount']= new DropdownLinkField("formToAccount", "To Account", "formToAccount");
        $fields['formToAccount']->setShowInTable(false);
        $fields['formToAccount']->setLinkInfo(get_class(new MoveLine()), "Account",get_class(new Account()));

        $fields['formAmount'] = new FloatField("formAmount", "Amount", "formAmount");
        $fields['formAmount']->setShowInTable(false);
        $fields['formAmount']->setMin(0);


        return $fields;

    }


    /**
     * if save is pressed, the data is saved to the sql table
     * @throws \Exception
     */
    function action_save_row($redirectOnSuccess = true)
    {



        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        } else {
            $c = $this->getCollectionObject();
        }
        //form view is over
        $v =  $this->checkPostValues();
        if($v === false){
            return false;
        }


        $Move = new Move();
//        $fields['formName']
        $Move->set("name",$v['formName']);

//        $fields['formReference']
        $Move->set("reference",$v['formReference']);
//        $fields['formStatus']
        $Move->set("status",$v['formStatus']);

//        $fields['formDate']
        $Move->set("date_posted",$v['formDate']);
//        $fields['formFromAccount']
        $Accounts['from']=BaseEntity::getBaseEntityFromProxy($v['formFromAccount']);
        $MoveLines[0] = new MoveLine();
        $MoveLines[0]->set("Account",$Accounts['from'] );
        $Accounts['from']->get("MoveLines")->add($MoveLines[0]);
        $MoveLines[0]->set("Move",$Move );
        $Move->get("MoveLines")->add($MoveLines[0]);
//        $fields['formAmount']
        $MoveLines[0]->set("debit",0 );
        $MoveLines[0]->set("credit",$v['formAmount'] );
        $MoveLines[0]->set("balance",(-1)*$v['formAmount'] );
        $MoveLines[0]->set("date_posted",$v['formDate'] );
        $MoveLines[0]->set("reconciled",0);


//        $fields['formToAccount']
        $Accounts['to']=BaseEntity::getBaseEntityFromProxy($v['formToAccount']);
        $MoveLines[1] = new MoveLine();
        $MoveLines[1]->set("Account",$Accounts['to'] );
        $Accounts['to']->get("MoveLines")->add($MoveLines[1]);
        $Move->get("MoveLines")->add($MoveLines[1]);
        $MoveLines[1]->set("Move",$Move );
//        $fields['formAmount']
        $MoveLines[1]->set("debit",$v['formAmount'] );
        $MoveLines[1]->set("credit",0);
        $MoveLines[1]->set("balance",$v['formAmount'] );
        $MoveLines[1]->set("date_posted",$v['formDate'] );
        $MoveLines[1]->set("reconciled",0);



        //check consistency
        $this->consistencyErrors = $Move->checkConsistency();
        if (count($this->consistencyErrors) > 0) {
            return $this->handleFormError();
        }

        //persist
        $this->getEntityManager()->persist($Move);
        $this->getEntityManager()->persist($MoveLines[0]);
        $this->getEntityManager()->persist($MoveLines[1]);
        $this->getEntityManager()->persist($Accounts['from']);
        $this->getEntityManager()->persist($Accounts['to']);




        $this->getEntityManager()->flush();




        $this->finishFormView();
        if($redirectOnSuccess) {
            $this->redirect($c->getCollectionPath());
        }


    }


    function getActions($object, $row = array())
    {
        //".$object->action('edit_row_form')."
        $string = "
    	<td class='actioncell'>
    	<form method='post' action='" . $object->action('edit_row_form') . "'>
    		<input type='hidden' name='rowid' value='" . $row['id'] . "'/>
    		<input type='hidden' name='action' value='edit' id='action_" . $row['id'] . "'>";



        $string .= "</form>
    	</td>";
        return $string;
    }

}
