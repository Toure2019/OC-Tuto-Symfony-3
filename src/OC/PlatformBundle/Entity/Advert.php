<?php
//UD 1-Entité propriétaire de "image" (ajout de la propriété "image" ici)
//UD 2-Entité Inverse de "Application" (nul besoin de rajouter de pté ici)
//UD 3-Entité propriétaire de "Category" (ajouter l'attr "categories")

/* BD 4-Entité Inverse de "Application (ajouter pté pr rendre Bidirection°)
    et on ajoute mappedBy="advert" ds l'annotation + Getter & Setter.
   BD 5- Inverse de "AdvertSkill" (ajout: mappedBy="advert")
*/
//BD 6-Entité propriétaire qui contiendra l'attribut "User" 

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use OC\PlatformBundle\Validator\Antiflood;

/**
 * Advert
 *
 * @ORM\Table(name="advert")
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Repository\AdvertRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="title", message="Une annonce existe déjà avec ce titre.")
 */
class Advert
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(min=10)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     * @Assert\Length(min=2)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     * @Antiflood()
     */
    private $content; 

    /**
     * @ORM\Column(name="published", type="boolean")
     */
    private $published = true;

    /**
     * @ORM\OneToOne(targetEntity="OC\PlatformBundle\Entity\Image", 
     * cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    private $image; 

    /**
     * @ORM\ManyToMany(targetEntity="OC\PlatformBundle\Entity\Category",
     * cascade={"persist"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="OC\PlatformBundle\Entity\Application",
     * mappedBy="advert")
     */
    private $applications;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="nb_applications", type="integer")
     */
    private $nbApplications = 0;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="OC\PlatformBundle\Entity\AdvertSkill",
     * mappedBy="advert")
     */
    private $advertSkills;

    /**
     * @ORM\ManyToOne(targetEntity="OC\UserBundle\Entity\User",
     * inversedBy="adverts")
     */
    private $user;


    /**
    * @Assert\IsTrue()
    */
    public function isAdvertValid()
    {
        return true;
    }

    /**
    * @Assert\IsTrue()
    */
    public function isTitle()
    {
        return true;
    }

    /**
    * @Assert\Callback
    */
    public function isContentValid(ExecutionContextInterface $context)
    {
        $forbiddenWords = array('démotivation', 'abandon');

        // On vérifie que le contenu ne contient pas l'un des mots
        if (preg_match('#'.implode('|', $forbiddenWords).'#', $this->getContent())) {
            // La règle est violée, on définit l'erreur
            $context
            ->buildViolation('Contenu invalide car il contient un mot interdit.') // message
            ->atPath('content')                                                   // attribut de l'objet qui est violé
            ->addViolation(); // ceci déclenche l'erreur, ne l'oubliez pas
        }
    }


    public function __construct()
    {
        $this->date = new \DateTime();
        $this->categories = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Advert
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Advert
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Advert
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Advert
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * Set published
     *
     * @param boolean $published
     *
     * @return Advert
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set image
     *
     * @param \OC\PlatformBundle\Entity\Image $image
     *
     * @return Advert
     */
    public function setImage(\OC\PlatformBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \OC\PlatformBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add category
     *
     * @param \OC\PlatformBundle\Entity\Category $category
     *
     * @return Advert
     */
    public function addCategory(\OC\PlatformBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \OC\PlatformBundle\Entity\Category $category
     */
    public function removeCategory(\OC\PlatformBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add application
     *
     * @param \OCPlatformBundle\Entity\Application $application
     *
     * @return Advert
     */
    public function addApplication(\OC\PlatformBundle\Entity\Application $application)
    {
        $this->applications[] = $application;

        // On lie l'annonce à la candidature (pr éviter incohérence)
        $application->setAdvert($this); // Un seul côté suffit

        return $this;
    }

    /**
     * Remove application
     *
     * @param \OCPlatformBundle\Entity\Application $application
     */
    public function removeApplication(\OC\PlatformBundle\Entity\Application $application)
    {
        $this->applications->removeElement($application);

        // Et si notre relation était facultative (nullable=true, 
        // ce qui n'est pas notre cas ici attention) :        
        // $application->setAdvert(null);
    }

    /**
     * Get applications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Advert
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set nbApplications
     *
     * @param integer $nbApplications
     *
     * @return Advert
     */
    public function setNbApplications($nbApplications)
    {
        $this->nbApplications = $nbApplications;

        return $this;
    }

    /**
     * Get nbApplications
     *
     * @return integer
     */
    public function getNbApplications()
    {
        return $this->nbApplications;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Advert
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
    


    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \Datetime());
    }

    public function increaseApplication()
    {
        $this->nbApplications++;
    }

    public function decreaseApplication()
    {
        $this->nbApplications--;
    }


    /**
     * Add advertSkill
     *
     * @param \OC\PlatformBundle\Entity\AdvertSkill $advertSkill
     *
     * @return Advert
     */
    public function addAdvertSkill(\OC\PlatformBundle\Entity\AdvertSkill $advertSkill)
    {
        $this->advertSkills[] = $advertSkill;

        return $this;
    }

    /**
     * Remove advertSkill
     *
     * @param \OC\PlatformBundle\Entity\AdvertSkill $advertSkill
     */
    public function removeAdvertSkill(\OC\PlatformBundle\Entity\AdvertSkill $advertSkill)
    {
        $this->advertSkills->removeElement($advertSkill);
    }

    /**
     * Get advertSkills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdvertSkills()
    {
        return $this->advertSkills;
    }

    /**
     * Set user
     *
     * @param \OC\UserBundle\Entity\User $user
     *
     * @return Advert
     */
    public function setUser(\OC\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \OC\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
