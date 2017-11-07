<?php

namespace Concrete\Package\BaclucAccountingPackage\Src\BlockOptions;

use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BasicTablePackage\Src\BaseEntityRepository;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Doctrine\ORM\Mapping\Entity;

/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/

/**
 * Class GroupRefOption
 * @IgnoreAnnotation("package")
 * @IgnoreAnnotation("package")\n*  Concrete\Package\BasicTablePackage\Src\BlockOptions
 * @Entity
 * @DiscriminatorEntry(value="Concrete\Package\BaclucAccountingPackage\Src\BlockOptions\AccountRefOption")
 */
class AccountRefOption extends TableBlockOption
{
    use EntityGetterSetter;
    /**
     * @var string
     * @Column(type="string")
     */
    protected $optionType = __CLASS__;

    /**
     * @var Account
     * @ManyToOne(targetEntity="Concrete\Package\BaclucAccountingPackage\Src\Account")
     */
    protected $Account;

    public function __construct ()
    {
        $this->optionType == __CLASS__;
        $this->setDefaultFieldTypes();
    }

    public function getLabel ()
    {
        return t('Which Accounts are used in this Block?');
    }

    public function getFieldType ()
    {
        if ($this->fieldTypes['Account'] == null) {
            $this->setDefaultFieldTypes();
        }
        if ($this->optionName != null) {
            $this->fieldTypes['Account']->setLabel($this->optionName);
            $this->fieldTypes['Account']->setPostName(str_replace(" ", "", $this->optionName));
        }
        return $this->fieldTypes['Account'];
    }

    public function getValue ()
    {
        if ($this->Account == null) {
            if ($this->getId() != null) {
                $query = BaseEntityRepository::getBuildQueryWithJoinedAssociations(get_class($this));
                $query->where($query->expr()->eq("e0." . $this->getIdFieldName(), ":id"));
                $query->setParameter(":id", $this->getId());
                $result = $query->getQuery()->getSingleResult();
                $this->Account = $result->get("Account");
            }
        }

        return $this->Account;
    }


    public function setValue ($Account)
    {
        $this->Account = $Account;
    }


}