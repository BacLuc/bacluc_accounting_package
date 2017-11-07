<?php

namespace Concrete\Package\BaclucAccountingPackage\Block\BaclucIncomeSheetBlock;

use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\CanEditOption;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
use Core;
use Loader;
use Page;
use User;

class Controller extends \Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Controller
{
    protected $btHandle = 'bacluc_income_sheet_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucIncomeSheetBlock";

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
     * @var Account[]
     */
    protected $accounts = array();


    /**
     * @var \DateTime
     */
    protected $startdate;

    /**
     * @var \DateTime
     */
    protected $enddate;

    /**
     *
     * Controller constructor.
     * @param null $obj
     */
    function __construct ($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new Account();
        parent::__construct($obj);


        if ($obj instanceof Block) {
            $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')
                       ->findOneBy(array( 'bID' => $obj->getBlockID() ))
            ;

            $this->basicTableInstance = $bt;
        }
        $this->loadRange();


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

    public function loadRange ()
    {
//read date from session if exists
        $sessionstartdate = $_SESSION[$this->getHTMLId() . "startdate"];
        $sessionenddate = $_SESSION[$this->getHTMLId() . "enddate"];

        if (strlen($sessionstartdate) == 0 && strlen($sessionenddate) == 0) {

            $date = new \DateTime();
            $this->startdate = new \DateTime($date->format("Y") . "-01-01");
            $this->enddate = new  \DateTime($date->format("Y") . "-12-31");
        }
        elseif (strlen($sessionstartdate) > 0 && strlen($sessionenddate) == 0) {
            $this->startdate = new  \DateTime($sessionstartdate);
            $this->enddate = new  \DateTime($this->startdate->format("Y") . "-12-31");
        }
        elseif (strlen($sessionstartdate) == 0 && strlen($sessionenddate) > 0) {
            $this->enddate = new  \DateTime($sessionenddate);
            $this->startdate = new  \DateTime($this->enddate->format("Y") . "-01-01");
        }
        else {
            $this->startdate = new  \DateTime($sessionstartdate);
            $this->enddate = new  \DateTime($sessionenddate);
        }


    }

    /**
     * @return string
     */
    public function getBlockTypeDescription ()
    {
        return t("Show Income Sheet");
    }

    //override all the old methods action methods to just show the block

    /**
     * @return string
     */
    public function getBlockTypeName ()
    {
        return t("Bacluc Income Sheet");
    }

    /**
     * action display form for new entry
     */
    function action_add_new_row_form ()
    {
        $this->action_save_row();

    }

    public function action_save_row ($redirectOnSuccess = true)
    {
        $bo = $this->getBlockObject();


        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        }
        else {
            $c = $this->getCollectionObject();
        }
        $this->redirect($c->getCollectionPath());

    }

    /**
     * action to open a form to edit/delete (manipulate) an existing row
     */
    function action_edit_row_form ()
    {
        $this->action_save_row();
    }

    public function view ()
    {
        $this->set("revenues", $this->getRevenues());
        $this->set("expenses", $this->getExpenses());

        $startDateField = new DateField("dummy", "Choose startdate", "startdate");
        $startDateField->setSQLValue($this->startdate);
        $this->set("startDateField", $startDateField);
        $endDateField = new DateField("dummy", "Choose enddate", "enddate");
        $endDateField->setSQLValue($this->enddate);
        $this->set("endDateField", $endDateField);

        $this->set("htmlid", $this->getHTMLId());
        $this->set("header", $this->getHeader());
    }

    /**
     * @return array
     */
    function getRevenues ()
    {
        $accounts = $this->getAccounts();
        $revenues = array();
        if (count($accounts) > 0) {
            foreach ($accounts as $key => $value) {
                if ($value->get("type") == Account::TYPE_RECIEVABLE) {
                    $revenues[$value->get("name")] =
                        (- 1) * $value->getBalanceBetweenDates($this->startdate, $this->enddate);
                }
            }
        }
        return $revenues;
    }

    public function getAccounts ()
    {

        if (count($this->accounts) > 0) {
            return $this->accounts;
        }
        $this->getEntityManager()->clear();
        //get all accounts and check their consistency
        $accountBlock = new \Concrete\Package\BaclucAccountingPackage\Block\BaclucAccountBlock\Controller();

        $query = BaseEntityRepository::getBuildQueryWithJoinedAssociations(get_class($accountBlock->getModel()));
        $query->where($query->expr()->in("e0.type", ":types"))
              ->setParameter(":types", array( Account::TYPE_PAYABLE, Account::TYPE_RECIEVABLE ))
        ;
        $modelList = $query->getQuery()->getResult();
        $this->accounts = $modelList;

        return $this->accounts;
    }

    function getExpenses ()
    {
        $accounts = $this->getAccounts();
        $expenses = array();
        if (count($accounts) > 0) {
            foreach ($accounts as $key => $value) {
                if ($value->get("type") == Account::TYPE_PAYABLE) {
                    $expenses[$value->get("name")] = $value->getBalanceBetweenDates($this->startdate, $this->enddate);
                }
            }
        }
        return $expenses;
    }

    public function action_set_range ()
    {
        $u = new User();


        $bo = $this->getBlockObject();


        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        }
        else {
            $c = $this->getCollectionObject();
        }

        if ($this->requiresRegistration()) {
            if (!$u->isRegistered()) {
                $this->redirect('/login');
            }
        }


        $antispam = Loader::helper('validation/antispam');
        if ($antispam->check('', 'survey_block')) { // we do a blank check which will still check IP and UserAgent's
            $duID = 0;
            if ($u->getUserID() > 0) {
                $duID = $u->getUserID();
            }

            /** @var \Concrete\Core\Permission\IPService $iph */
            $iph = Core::make('helper/validation/ip');
            $ip = $iph->getRequestIP();
            $ip = ($ip === false) ? ('') : ($ip->getIp($ip::FORMAT_IP_STRING));
            $v = array();

            $startdateset = false;
            if (isset($_POST['startdate'])) {
                $dateField = new DateField("dummy", "dummy", "dummy");
                if ($dateField->validatePost($_POST['startdate'])) {
                    $newdate = $dateField->getSQLValue();
                    $this->startdate = $newdate;
                    $_SESSION[$this->getHTMLId() . "startdate"] = $this->startdate->format("Y-m-d");
                    $startdateset = true;
                }
            }
            if (isset($_POST['enddate'])) {
                $dateField = new DateField("dummy", "dummy", "dummy");
                if ($dateField->validatePost($_POST['enddate'])) {
                    $newdate = $dateField->getSQLValue();

                    if ($startdateset) {
                        if ($this->startdate < $newdate) {
                            $this->enddate = $newdate;
                            $_SESSION[$this->getHTMLId() . "enddate"] = $this->enddate->format("Y-m-d");
                        }
                        else {
                            //display error
                        }
                    }
                    else {
                        $_SESSION[$this->getHTMLId() . "enddate"] = $this->enddate->format("Y-m-d");
                    }

                }
            }
        }

        //at the end, anyway show same page again
        $this->action_save_row();
    }

}
