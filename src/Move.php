<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */

namespace Concrete\Package\BaclucAccountingPackage\Src;

use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\Exceptions\ConsistencyCheckException;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityMultipleField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;


/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/

/**
 * Class MoveLine
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\Move")
 * @Entity
@Table(name="bacluc_move"
 * )
 *
 */
class Move extends BaseEntity
{
    use EntityGetterSetter;
    //dontchange
    const STATUS_DRAFT  = 'draft'; //that you have a filter that is only for this entity
    const STATUS_POSTED = 'posted';
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
    protected $name;
    /**
     * @Column(type="string")
     */
    protected $reference;
    /**
     * @Column(type="string")
     */
    protected $status;
    /**
     * @Column(type="date")
     */
    protected $date_posted;
    /**
     * @var MoveLine[]
     * @OneToMany(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\MoveLine", mappedBy="Move")
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
        $this->fieldTypes['status'] = new DropdownField('status', 'Status', 'poststatus');
        $refl = new \ReflectionClass($this);
        $constants = $refl->getConstants();
        $userConstants = array();
        foreach ($constants as $key => $value) {
            $userConstants[$value] = $value;
        }
        /**
         * @var DropdownField
         */
        $this->fieldTypes['status']->setOptions($userConstants);


        $MoveLines = $this->fieldTypes['MoveLines'];
        $directEditField = new DirectEditAssociatedEntityMultipleField($MoveLines->getSQLFieldName(), "Move Lines",
                                                                       $MoveLines->getPostName());
        DropdownLinkField::copyLinkInfo($MoveLines, $directEditField);
        $directEditField->setAlwaysCreateNewInstance(true);
        $this->fieldTypes['MoveLines'] = $directEditField;

    }

    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction ()
    {
        $function = function (Move $item) {
            $returnString = '';
            if (strlen($item->name) > 0) {
                $returnString .= $item->name . " ";
            }
            return $returnString;
        };
        return $function;
    }

    public function setDefaultValues ()
    {
        $this->fieldTypes['date_posted']->setDefault(new \DateTime());
        return $this;
    }

    public function checkConsistency ()
    {
        //first check movelines
        $errors = array();

        if ($this->checkingConsistency) {
            throw new ConsistencyCheckException();
        }
        $this->checkingConsistency = true;

        $totalCredit = 0;
        $totalDebit = 0;
        /**
         * @var MoveLine $moveLine
         */
        if (count($this->MoveLines) > 0) {
            foreach ($this->MoveLines->toArray() as $moveLine) {

                $moveLine = BaseEntityRepository::getBaseEntityFromProxy($moveLine);
                try {
                    $moveLineErrors = $moveLine->checkConsistency();
                }
                catch (ConsistencyCheckException $e) {

                }
                if (count($moveLineErrors) > 0) {
                    foreach ($moveLineErrors as $error) {
                        $errors[] = $error;
                    }
                }

                $totalCredit += $moveLine->credit;
                $totalDebit += $moveLine->debit;
            }
        }
        if (abs($totalCredit - $totalDebit) > 10e-5) {
            $errors[] = "The total debit and total credit of a move must be balanced.";
        }


        $this->getEntityManager()->persist($this);
        $this->checkingConsistency = false;
        return $errors;
    }

}