<?php

namespace Concrete\Package\BaclucAccountingPackage\Block\BaclucConfiguredMoveBlock;

use Concrete\Package\BaclucAccountingPackage\Src\BlockOptions\AccountRefOption;
use Concrete\Package\BaclucAccountingPackage\Src\Move;
use Concrete\Package\BaclucAccountingPackage\Src\MoveLine;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\CanEditOption;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TextBlockOption;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\Field as Field;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FloatField;
use Doctrine\ORM\QueryBuilder;
use Page;

class Controller extends \Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Controller
{
    protected $btHandle = 'bacluc_configured_move_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucConfiguredMoveBlock";

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
    function __construct ($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new Move();


        /*
         * add blockoptions here if you wish
         * */
        $this->requiredOptions = array(
            new TextBlockOption(),
            new AccountRefOption(),
            new AccountRefOption(),
        );

        $this->requiredOptions[0]->set('optionName', "Name of Configured Move");
        $this->requiredOptions[1]->set('optionName', "From Account");


        $this->requiredOptions[2]->set('optionName', "To Account");

        parent::__construct($obj);


        if ($obj instanceof Block) {
            $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')
                       ->findOneBy(array( 'bID' => $obj->getBlockID() ))
            ;

            $this->basicTableInstance = $bt;
        }


    }


    /**
     * @return string
     */
    public function getBlockTypeDescription ()
    {
        return t("Create Moves configured");
    }

    /**
     * @return string
     */
    public function getBlockTypeName ()
    {
        return t("Bacluc Configured Move");
    }


    /**
     * @return array of Application\Block\BasicTableBlock\Field
     */
    public function getFields ()
    {
        if ($this->editKey == null) {
            $fields = $this->model->getFieldTypes();
        }
        else {
            $fields = $this->getEntityManager()->getRepository(get_class($this->model))
                           ->findOneBy(array( $this->model->getIdFieldName() => $this->editKey ))->getFieldTypes()
            ;
        }
        //set all fields to not display in form view
        foreach ($fields as $sqlFieldName => &$FieldType) {
            /**
             * @var Field $FieldType
             */
            $FieldType->setShowInForm(false);
        }

        //add the fields which should be displayed in form view
        $fields['formName'] = new Field("formName", "Name", "formName");
        $fields['formName']->setShowInTable(false);

        $fields['formReference'] = new Field("formReference", "Reference", "formReference");
        $fields['formReference']->setShowInTable(false);

        $fields['formStatus'] = new DropdownField("formStatus", "Status", "formStatus");
        $fields['formStatus']->setShowInTable(false);
        $fields['formStatus']->setOptions($fields['status']->getOptions());

        $fields['formDate'] = new DateField("formDate", "Date", "formDate");
        $fields['formDate']->setDefault(new \DateTime());
        $fields['formDate']->setShowInTable(false);


        $fields['formAmount'] = new FloatField("formAmount", "Amount", "formAmount");
        $fields['formAmount']->setShowInTable(false);
        $fields['formAmount']->setMin(0);


        return $fields;

    }
    /*
        public function action_add_new_row_form()
        {
            parent::action_add_new_row_form(); // TODO: Change the autogenerated stub
            $this->view();
        }
    */

    /**
     * if save is pressed, the data is saved to the sql table
     * @throws \Exception
     */
    function action_save_row ($redirectOnSuccess = true)
    {


        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        }
        else {
            $c = $this->getCollectionObject();
        }
        //form view is over
        $v = $this->checkPostValues();
        if ($v === false) {
            return false;
        }


        $Move = new Move();
//        $fields['formName']
        $Move->set("name", $v['formName']);

//        $fields['formReference']
        $Move->set("reference", $v['formReference']);
//        $fields['formStatus']
        $Move->set("status", $v['formStatus']);

//        $fields['formDate']
        $Move->set("date_posted", $v['formDate']);
//        $fields['formFromAccount']

        $options = $this->getBlockOptions();
        $account = $options[1]->getValue();
        $Accounts['from'] = BaseEntityRepository::getBaseEntityFromProxy($account);
        $MoveLines[0] = new MoveLine();
        $MoveLines[0]->set("Account", $Accounts['from']);
        $Accounts['from']->get("MoveLines")->add($MoveLines[0]);
        $MoveLines[0]->set("Move", $Move);
        $Move->get("MoveLines")->add($MoveLines[0]);
//        $fields['formAmount']
        $MoveLines[0]->set("debit", 0);
        $MoveLines[0]->set("credit", $v['formAmount']);
        $MoveLines[0]->set("balance", (- 1) * $v['formAmount']);
        $MoveLines[0]->set("date_posted", $v['formDate']);
        $MoveLines[0]->set("reconciled", 0);


        $account = $options[2]->getValue();
        $Accounts['to'] = BaseEntityRepository::getBaseEntityFromProxy($account);
        $MoveLines[1] = new MoveLine();
        $MoveLines[1]->set("Account", $Accounts['to']);
        $Accounts['to']->get("MoveLines")->add($MoveLines[1]);
        $Move->get("MoveLines")->add($MoveLines[1]);
        $MoveLines[1]->set("Move", $Move);
//        $fields['formAmount']
        $MoveLines[1]->set("debit", $v['formAmount']);
        $MoveLines[1]->set("credit", 0);
        $MoveLines[1]->set("balance", $v['formAmount']);
        $MoveLines[1]->set("date_posted", $v['formDate']);
        $MoveLines[1]->set("reconciled", 0);


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
        if ($redirectOnSuccess) {
            $this->redirect($c->getCollectionPath());
        }


    }


    function getActions ($object, $row = array())
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

    public function view ()
    {
        $options = $this->getBlockOptions();
        $this->set("transactionName", $options[0]->getValue());
    }

    /**
     * @param QueryBuilder $query
     * @param array $queryConfig
     * @return QueryBuilder
     */
    public function addFilterToQuery (QueryBuilder $query, array $queryConfig = array())
    {
        //first, check if entities are set right
        $error = true;
        if (isset($queryConfig['MoveLines'])) {
            $targetEntity = $queryConfig['MoveLines']['class'];
            $reflection = new \ReflectionClass($targetEntity);
            $MoveLine = new MoveLine();
            $account1 = null;
            $account2 = null;
            if ($reflection->isSubclassOf(get_class($MoveLine)) || $targetEntity == get_class($MoveLine)) {
                //check if MoveLine has still property Account
                if (property_exists(get_class($MoveLine), "Account")) {
                    //check if options are set
                    $blockOptions = $this->getBlockOptions();
                    //TODO change the numbers here
                    $account1 = $blockOptions[1]->getValue();
                    $account2 = $blockOptions[2]->getValue();
                    if ($account1 != null && $account2 != null) {
                        $error = false;
                    }
                }
            }
        }
        if ($error === false) {
            /**
             * @var QueryBuilder $query
             */

            $subquery1 = $this->getEntityManager()->createQueryBuilder();
            $subquery2 = $this->getEntityManager()->createQueryBuilder();
            $query->andWhere($query->expr()->andX(
                $query->expr()->exists(
                    $subquery1->select("notused1")
                              ->from($queryConfig['MoveLines']['class'], "notused1")
                              ->leftJoin("notused1.Account", "a1")
                              ->leftJoin("notused1.Move", "m1")
                              ->where(
                                  $query->expr()->andX(
                                      $query->expr()->eq("a1", ":ConfiguredAccount1")
                                      , $query->expr()->eq("m1", "e0")
                                  )
                              )
                )
                , $query->expr()->exists(
                $subquery2->select("notused2")
                          ->from($queryConfig['MoveLines']['class'], "notused2")
                          ->leftJoin("notused2.Account", "a2")
                          ->leftJoin("notused2.Move", "m2")
                          ->where(
                              $query->expr()->andX(
                                  $query->expr()->eq("a2", ":ConfiguredAccount2")
                                  , $query->expr()->eq("m2", "e0")
                              )
                          )
            )
            ));
            $query->setParameter("ConfiguredAccount1", $account1)
                  ->setParameter("ConfiguredAccount2", $account2)
            ;
        }

        return $query;
    }


}
