<?php

namespace OC\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="OC\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="OC\PlatformBundle\Entity\Advert",
     * mappedBy="user")
     */
    private $adverts;



    
    /**
     * Add advert
     *
     * @param \OC\PlatformBundle\Entity\Advert $advert
     *
     * @return User
     */
    public function addAdvert(\OC\PlatformBundle\Entity\Advert $advert)
    {
        $this->adverts[] = $advert;

        return $this;
    }

    /**
     * Remove advert
     *
     * @param \OC\PlatformBundle\Entity\Advert $advert
     */
    public function removeAdvert(\OC\PlatformBundle\Entity\Advert $advert)
    {
        $this->adverts->removeElement($advert);
    }

    /**
     * Get adverts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdverts()
    {
        return $this->adverts;
    }


    
    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="username", type="string", length=255, unique=true)
    //  */
    // private $username;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="password", type="string", length=255)
    //  */
    // private $password;

    // /**
    //  * @var string
    //  *
    //  * @ORM\Column(name="salt", type="string", length=255)
    //  */
    // private $salt;

    // /**
    //  * @var array
    //  *
    //  * @ORM\Column(name="roles", type="array")
    //  */
    // private $roles = array();


    // /**
    //  * Get id
    //  *
    //  * @return int
    //  */
    // public function getId()
    // {
    //     return $this->id;
    // }

    // /**
    //  * Set username
    //  *
    //  * @param string $username
    //  *
    //  * @return User
    //  */
    // public function setUsername($username)
    // {
    //     $this->username = $username;

    //     return $this;
    // }

    // /**
    //  * Get username
    //  *
    //  * @return string
    //  */
    // public function getUsername()
    // {
    //     return $this->username;
    // }

    // /**
    //  * Set password
    //  *
    //  * @param string $password
    //  *
    //  * @return User
    //  */
    // public function setPassword($password)
    // {
    //     $this->password = $password;

    //     return $this;
    // }

    // /**
    //  * Get password
    //  *
    //  * @return string
    //  */
    // public function getPassword()
    // {
    //     return $this->password;
    // }

    // /**
    //  * Set salt
    //  *
    //  * @param string $salt
    //  *
    //  * @return User
    //  */
    // public function setSalt($salt)
    // {
    //     $this->salt = $salt;

    //     return $this;
    // }

    // /**
    //  * Get salt
    //  *
    //  * @return string
    //  */
    // public function getSalt()
    // {
    //     return $this->salt;
    // }

    // /**
    //  * Set roles
    //  *
    //  * @param array $roles
    //  *
    //  * @return User
    //  */
    // public function setRoles($roles)
    // {
    //     $this->roles = $roles;

    //     return $this;
    // }

    // /**
    //  * Get roles
    //  *
    //  * @return array
    //  */
    // public function getRoles()
    // {
    //     return $this->roles;
    // }


    // public function eraseCredentials()
    // {

    // }
}