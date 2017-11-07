<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */

namespace Concrete\Package\BaclucAccountingPackage\Src;

use Concrete\Core\Package\Package;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\Exceptions\ConsistencyCheckException;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;


/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/

/**
 * Class Account
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\Account")
 * @Entity
@Table(name="bacluc_account"
 * )
 *
 */
class Account extends BaseEntity
{
    use EntityGetterSetter;

    const TYPE_OTHER      = 'other';
    const TYPE_RECIEVABLE = 'recievable';
    const TYPE_PAYABLE    = 'payable';
    const TYPE_LIQUIDITY  = 'liquidity'; //that you have a filter that is only for this entity
    //dontchange
    public static $staticEntityfilterfunction;
    /**
     * @var int
     * @Id @Column(type="integer", nullable=false, options={"unsigned":true})
     * @GEneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Column(type="string")
     */
    protected $code;
    /**
     * @Column(type="string")
     */
    protected $name;
    /**
     * @Column(type="string")
     */
    protected $type;
    /**
     * @Column(type="float")
     */
    protected $credit;
    /**
     * @Column(type="float")
     */
    protected $debit;
    /**
     * @Column(type="float")
     */
    protected $balance;
    /**
     * @var MoveLine[]
     * @OneToMany(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\MoveLine", mappedBy="Account")
     */
    protected $MoveLines;

    public function __construct ()
    {
        parent::__construct();


        if ($this->MoveLines == null) {
            $this->MoveLines = new ArrayCollection();
        }
        $this->setDefaultFieldTypes();
    }

    public function setDefaultFieldTypes ()
    {
        parent::setDefaultFieldTypes();

        $this->fieldTypes['debit']->setShowInForm(false);
        $this->fieldTypes['credit']->setShowInForm(false);
        $this->fieldTypes['balance']->setShowInForm(false);
        $this->fieldTypes['MoveLines']->setShowInForm(false);

        $this->fieldTypes['type'] = new DropdownField('type', 'Type', 'posttype');
        $refl = new \ReflectionClass($this);
        $constants = $refl->getConstants();
        $userConstants = array();
        foreach ($constants as $key => $value) {
            $userConstants[$value] = $value;
        }
        /**
         * @var DropdownField
         */
        $this->fieldTypes['type']->setOptions($userConstants);


    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction ()
    {
        $function = function (Account $item) {
            $item = BaseEntityRepository::getBaseEntityFromProxy($item);
            $returnString = '';
            if (strlen($item->code) > 0) {
                $returnString .= $item->code . " ";
            }
            if (strlen($item->name) > 0) {
                $returnString .= $item->name . " ";
            }
            if (strlen($item->balance) != 0) {
                $returnString .= $item->balance . " ";
            }
            return $returnString;
        };
        return $function;
    }

    public static function checkAccountsConsistency ()
    {
        $pkg = Package::getByHandle("basic_table_package");
        $em = $pkg->getEntityManager();
//get all accounts and check their consistency
        $accountBlock = new \Concrete\Package\BaclucAccountingPackage\Block\BaclucAccountBlock\Controller();

        $query = BaseEntityRepository::getBuildQueryWithJoinedAssociations(get_class($accountBlock->getModel()));
        $modelList = $query->getQuery()->getResult();
        if (count($modelList) > 0) {
            /**
             * @var Account $account
             */
            foreach ($modelList as $key => $account) {
                $account->checkConsistency();
                $em->persist($account);

            }
        }
        $em->flush();
    }

    public function checkConsistency ()
    {
        $errors = array();
        if ($this->checkingConsistency) {
            throw new ConsistencyCheckException();
        }
        $this->checkingConsistency = true;
        $totalDebit = 0;
        $totalCredit = 0;
        $totalBalance = 0;
        if (count($this->MoveLines) == 0) {

        }
        else {
            foreach ($this->MoveLines->toArray() as &$MoveLine) {
                $MoveLine = BaseEntityRepository::getBaseEntityFromProxy($MoveLine);
                /**
                 * @var MoveLine $MoveLine
                 */
                try {
                    $movelineErrors = $MoveLine->checkConsistency();
                    foreach ($movelineErrors as $key => $value) {
                        $errors[] = $value;
                    }
                }
                catch (ConsistencyCheckException $e) {

                }

                $totalCredit += $MoveLine->credit;
                $totalDebit += $MoveLine->debit;
                $totalBalance += $MoveLine->balance;

            }
        }

        if (abs($totalBalance - ($totalDebit - $totalCredit)) > 10e-5) {
            $errors[] = "The difference of totalDebit and totalCredit does not correspond to the sum of totalBalance.";
        }

        $this->credit = $totalCredit;
        $this->debit = $totalDebit;
        $this->balance = $totalBalance;

        $this->checkingConsistency = false;
        $this->getEntityManager()->persist($this);

        return $errors;
    }

    public function getBalanceUntilDate (\DateTime $date)
    {
        if (count($this->MoveLines) > 0) {
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($this->MoveLines as $moveLine) {
                /**
                 * @var MoveLine $moveLine
                 */
                if ($moveLine->get("date_posted") <= $date) {

                    $totalDebit += $moveLine->get("debit");
                    $totalCredit += $moveLine->get("credit");
                }

            }

            return $totalDebit - $totalCredit;
        }
        return 0;
    }

    public function getBalanceBetweenDates (\DateTime $startdate, \DateTime $enddate)
    {
        if (count($this->MoveLines) > 0) {
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($this->MoveLines as $moveLine) {
                /**
                 * @var MoveLine $moveLine
                 */
                if ($moveLine->get("date_posted") >= $startdate
                    && $moveLine->get("date_posted") <= $enddate) {

                    $totalDebit += $moveLine->get("debit");
                    $totalCredit += $moveLine->get("credit");
                }

            }

            return $totalDebit - $totalCredit;
        }
        return 0;
    }

}