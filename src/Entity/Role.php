<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @ORM\Table(name="role", uniqueConstraints={@ORM\UniqueConstraint(name="rolename_UNIQUE", columns={"rolename"})})
 * @ORM\Entity
 */
class Role
{
    /**
     * @var int
     *
     * @ORM\Column(name="idrole", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idrole;

    /**
     * @var string
     *
     * @ORM\Column(name="rolename", type="string", length=100, nullable=false)
     */
    private $rolename;

    /**
     * @var string
     *
     * @ORM\Column(name="rolevalue", type="string", length=45, nullable=false)
     */
    private $rolevalue;

    /**
     * @var string
     *
     * @ORM\Column(name="rolecol", type="string", length=20, nullable=false)
     */
    private $rolecol;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="roleIdrole")
     */
    private $userIduser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userIduser = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getIdrole(): ?int
    {
        return $this->idrole;
    }

    public function getRolename(): ?string
    {
        return $this->rolename;
    }

    public function setRolename(string $rolename): self
    {
        $this->rolename = $rolename;

        return $this;
    }

    public function getRolevalue(): ?string
    {
        return $this->rolevalue;
    }

    public function setRolevalue(string $rolevalue): self
    {
        $this->rolevalue = $rolevalue;

        return $this;
    }

    public function getRolecol(): ?string
    {
        return $this->rolecol;
    }

    public function setRolecol(string $rolecol): self
    {
        $this->rolecol = $rolecol;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUserIduser(): Collection
    {
        return $this->userIduser;
    }

    public function addUserIduser(User $userIduser): self
    {
        if (!$this->userIduser->contains($userIduser)) {
            $this->userIduser[] = $userIduser;
            $userIduser->addRoleIdrole($this);
        }

        return $this;
    }

    public function removeUserIduser(User $userIduser): self
    {
        if ($this->userIduser->removeElement($userIduser)) {
            $userIduser->removeRoleIdrole($this);
        }

        return $this;
    }

}
