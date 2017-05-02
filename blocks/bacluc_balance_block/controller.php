<?php
namespace Concrete\Package\BaclucAccountingPackage\Block\BaclucBalanceBlock;

use Concrete\Core\Form\Service\Widget\DateTime;
use Concrete\Core\Package\Package;
use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BaclucEventPackage\Src\Event;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\DropdownBlockOption;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption;
use Concrete\Core\Block\BlockController;
use Concrete\Package\BasicTablePackage\Src\BasicTableInstance;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TextBlockOption;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
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
    protected $btHandle = 'bacluc_balance_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucBalanceBlock";

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
     * @var array (accountname => accountbalance)
     */
    protected $accounts = array();


    /**
     * @var string
     * year with 4 digits
     */
    protected $year;

    /**
     *
     * Controller constructor.
     * @param null $obj
     */
    function __construct($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new Account();
        parent::__construct($obj);






        if ($obj instanceof Block) {
         $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')->findOneBy(array('bID' => $obj->getBlockID()));

            $this->basicTableInstance = $bt;
        }


        //read date from session if exists
        $sessionyear = $_SESSION[$this->getHTMLId() . "year"];

        if(strlen($sessionyear)==0){

            $date = new \DateTime();
            $this->year = $date->format("Y");
        }else{
            $this->year = $sessionyear;
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
        return t("Show Balance");
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return t("BaclucBalanceBlock");
    }

    //override all the old methods action methods to just show the block

    public function action_save_row($redirectOnSuccess = true)
    {
        $bo = $this->getBlockObject();


        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        } else {
            $c = $this->getCollectionObject();
        }
        $this->redirect($c->getCollectionPath());

    }

    /**
     * action display form for new entry
     */
    function action_add_new_row_form()
    {
        $this->action_save_row();

    }

    /**
     * action to open a form to edit/delete (manipulate) an existing row
     */
    function action_edit_row_form()
    {
        $this->action_save_row();
    }



    public function getAccountsAndBalances(){

        if(count($this->accounts)>0){
            return $this->accounts;
        }
        $this->getEntityManager()->clear();
        //get all accounts and check their consistency
        $accountBlock = new \Concrete\Package\BaclucAccountingPackage\Block\BaclucAccountBlock\Controller();

        $query =BaseEntityRepository::getBuildQueryWithJoinedAssociations(get_class($accountBlock->getModel()));
        $modelList = $query->getQuery()->getResult();

        $enddate = new \DateTime($this->year."-12-31");
        if(count($modelList)>0){
                foreach($modelList as $account){
                    /**
                     * @var Account $account
                     */
                    $this->accounts[$account->get("name")] = $account->getBalanceUntilDate($enddate);
                }
        }

        return $this->accounts;
    }

    function getDebitors(){
        $accounts = $this->getAccountsAndBalances();
        $debitors = array();
         if (count($accounts)>0) {
             foreach ($accounts as $key => $value) {
                 if ($value >= 0) {
                     $debitors[$key] = $value;
                 }
             }
         }
        return $debitors;
    }

    function getCreditors(){
        $accounts = $this->getAccountsAndBalances();
        $creditors = array();
        if(count($accounts)>0) {
            foreach ($accounts as $key => $value) {
                if ($value < 0) {
                    $creditors[$key] = (-1)*$value;
                }
            }
        }
        return $creditors;
    }

    public function view(){
        $this->set("debitors", $this->getDebitors());
        $this->set("creditors", $this->getCreditors());
        $dateField = new DateField("dummy", "Choose year", "date");
        $dateField->setSQLValue(new \DateTime($this->year."-12-31"));
        $this->set("dateField", $dateField);
        $this->set("htmlid", $this->getHTMLId());
        $this->set("header", $this->getHeader());
    }

    public function action_set_date(){
        $u = new User();


        $bo = $this->getBlockObject();


        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        } else {
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

            if(isset($_POST['date'])){
                $dateField = new DateField("dummy", "dummy", "dummy");
                if($dateField->validatePost($_POST['date'])){
                    $newdate = $dateField->getSQLValue();
                    $this->year = $newdate->format("Y");
                     $_SESSION[$this->getHTMLId() . "year"] = $this->year;
                }
            }


        }

        //at the end, anyway show same page again
        $this->action_save_row();
    }

}
