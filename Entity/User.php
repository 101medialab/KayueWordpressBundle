<?php

namespace Kayue\WordpressBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kayue\WordpressBundle\Annotation as Wordpress;
use Symfony\Component\Validator\Constraints as Constraints;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: "users")]
#[ORM\Entity]
#[UniqueEntity(fields: ["email"], message: "Sorry, that email address is already used.")]
#[UniqueEntity(fields: ["username"], message: "Sorry, that username is already used.")]
#[UniqueEntity(fields: ["nicename"], message: "Sorry, that nicename is already used.")]
#[UniqueEntity(fields: ["displayName"], message: "Sorry, that display name has already been taken.")]
#[ORM\HasLifecycleCallbacks]
#[Wordpress\WordpressTable]
class User implements UserInterface
{
    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "ID", type: "wordpressid", length: 20)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    protected $id;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_login", type: "string", length: 60, unique: true)]
    #[Constraints\NotBlank]
    protected $username;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_pass", type: "string", length: 64)]
    #[Constraints\NotBlank]
    protected $password;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_nicename", type: "string", length: 64)]
    #[Constraints\NotBlank]
    protected $nicename;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_email", type: "string", length: 100)]
    #[Constraints\NotBlank]
    #[Constraints\Email]
    protected $email;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_url", type: "string", length: 100)]
    #[Constraints\Url]
    protected $url = '';

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_registered", type: "datetime")]
    protected $registeredDate;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_activation_key", type: "string", length: 60)]
    protected $activationKey = '';

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "user_status", type: "integer", length: 11)]
    protected $status = 0;

    /**
     * {@inheritdoc}
     */
    #[ORM\Column(name: "display_name", type: "string", length: 250)]
    #[Constraints\NotBlank]
    protected $displayName;

    /**
     * {@inheritdoc}
     */
    #[ORM\OneToMany(targetEntity: UserMeta::class, mappedBy: "user", cascade: ["persist"])]
    protected $metas;

    /**
     * {@inheritdoc}
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: "user")]
    protected $posts;

    /**
     * {@inheritdoc}
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "user")]
    protected $comments;

    public function __construct()
    {
        $this->metas = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set nicename
     *
     * @param string $nicename
     */
    public function setNicename($nicename)
    {
        $this->nicename = $nicename;
    }

    /**
     * Get nicename
     *
     * @return string
     */
    public function getNicename()
    {
        return $this->nicename;
    }

    /**
     * Set email
     *
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set registeredDate
     *
     * @param $registeredDate
     */
    public function setRegisteredDate($registeredDate)
    {
        $this->registeredDate = $registeredDate;
    }

    /**
     * Get registeredDate
     *
     * @return \DateTime
     */
    public function getRegisteredDate()
    {
        return $this->registeredDate;
    }

    /**
     * Set activationKey
     *
     * @param string $activationKey
     */
    public function setActivationKey($activationKey)
    {
        $this->activationKey = $activationKey;
    }

    /**
     * Get activationKey
     *
     * @return string
     */
    public function getActivationKey()
    {
        return $this->activationKey;
    }

    /**
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Add meta
     *
     * @param UserMeta $meta
     */
    public function addMeta(UserMeta $meta)
    {
        $this->metas[] = $meta;

        $meta->setUser($this);
    }

    /**
     * Get metas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMetas()
    {
        return $this->metas;
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return \Symfony\Component\Security\Core\Role\Role[] The user roles
     */
    public function getRoles(): array
    {
        $roles = array();
        $metas = $this->getMetas()->filter(function (UserMeta $meta) {
            return $meta->getKey() === 'wp_capabilities';
        });

        if ($metas->isEmpty()) {
            return array();
        }

        $capabilities = $metas->first()->getValue();

        if (!is_array($capabilities)) {
            return array();
        }

        foreach ($capabilities as $role => $value) {
            $roles[] = 'ROLE_WP_'.strtoupper($role);
        }

        return $roles;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials(): void
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->username,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->id,
            $this->username,
        ] = $data;
    }

    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->registeredDate = new \DateTime('now');
    }
}
