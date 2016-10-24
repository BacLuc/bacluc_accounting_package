<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */
namespace Concrete\Package\BaclucAccountingPackage\Src;
use Concrete\Package\BaclucAccountingPackage\Src\EntityViews\MoveLineFormView;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField as DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FileField as FileField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\WysiwygField as WysiwygField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Concrete\Package\BaclucPersonPackage\Src\Address;
use Concrete\Package\BaclucPersonPackage\Src\PostalAddress;


/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Doctrine\ORM\Mapping\Table;

/**
 * Class MoveLine
 * Package  Concrete\Package\BaclucAccountingPackage\Src
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\Move")
 * @Entity
@Table(name="bacluc_move_line"
)
 *
 */
class MoveLine extends BaseEntity
{
    use EntityGetterSetter;
    /**
     * @var int
     * @Id @Column(type="integer", nullable=false, options={"unsigned":true})
     * @GEneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="float")
     */
    protected $debit;

    /**
     * @Column(type="float")
     */
    protected $credit;

    /**
     * @Column(type="float")
     */
    protected $balance;

    /**
     * @Column(type="boolean")
     */
    protected $reconciled;

    /**
     * @Column(type="date")
     */
    protected $date_posted;

    /**
     * @var Account
     * @ManyToOne(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\Account" ,inversedBy="MoveLines")
     */
    protected $Account;


    /**
     * @var Move
     * @ManyToOne(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\Move" ,inversedBy="MoveLines")
     */
    protected $Move;


    public function __construct(){
        parent::__construct();

        $this->setDefaultFieldTypes();
    }

    public function setDefaultFormViews()
    {
        $this->defaultFormView = new MoveLineFormView($this);
    }

    public function setDefaultFieldTypes(){
        parent::setDefaultFieldTypes();
        /**
         * @var Field[] $this->FieldTypes
         */
        $this->fieldTypes['Move']->setShowInForm(false);

    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction(){
        $function = function(MoveLine $item){
            $returnString = '';
            return $returnString;
        };
        return $function;
    }

}