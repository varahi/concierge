<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Objet;

/**
 * Document
 *
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Document
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=255)
     */
    private $extension;

    /**
     * Many Documents have one (the same) Objet
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="documents")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    private $objet;


    private $file;

    // Temporary store the file name
    private $tempFilename;

    /**
     * @var string
     *
     * @ORM\Column(name="suffixDirectoryName", type="string", length=255, nullable=true)
     */
    private $suffixDirectoryName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="imageName", type="string", length=255, nullable=true)
     */
    private $imageName;


    public function __toString(): string
    {
        return $this->name;
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
     * @return string
     */
    public function getImageName(): string
    {
        return $this->id .'.' . $this->extension;
    }

    /**
     * @return string|null
     */
    public function getSuffixDirectoryName(): ?string
    {
        if (is_null($this->suffixDirectoryName)) {
            return null;
        } else {
            return $this->suffixDirectoryName;
        }
    }

    /**
     * @param string $suffixDirectoryName
     */
    public function setSuffixDirectoryName(string $suffixDirectoryName): void
    {
        $this->suffixDirectoryName = $suffixDirectoryName;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return Document
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // Replacing a file ? Check if we already have a file for this entity
        if (null !== $this->extension) {
            // Save file extension so we can remove it later
            $this->tempFilename = $this->extension;

            // Reset values
            $this->extension = null;
            $this->name = null;
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        // If no file is set, do nothing
        if (null === $this->file) {
            return;
        }

        // The file name is the entity's ID
        // We also need to store the file extension
        $this->extension = $this->file->guessExtension();

        // And we keep the original name
        $this->name = $this->file->getClientOriginalName();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // If no file is set, do nothing
        if (null === $this->file) {
            return;
        }

        // A file is present, remove it
        if (null !== $this->tempFilename) {
            $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempFilename;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move the file to the upload folder
        $this->file->move(
            $this->getUploadRootDir(),
            $this->id.'.'.$this->extension
        );
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // Save the name of the file we would want to remove
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->extension;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // PostRemove => We no longer have the entity's ID => Use the name we saved
        if (file_exists($this->tempFilename)) {
            // Remove file
            unlink($this->tempFilename);
        }
    }

    public function getUploadDir()
    {
        // Upload directory
        return 'uploads/documents/';
        // This means /web/uploads/documents/
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        // Image location (PHP)
        //return __DIR__.'/../../../../web/'.$this->getUploadDir();
        return __DIR__.'/../../public_html/'.$this->getUploadDir().$this->getSuffixDirectoryName();
    }

    public function getUrl()
    {
        return $this->id.'.'.$this->extension;
    }

    /**
     * Set objet
     *
     * @param Objet $objet
     *
     * @return Document
     */
    public function setObjet(Objet $objet = null)
    {
        $this->objet = $objet;
        return $this;
    }

    /**
     * Get objet
     *
     * @return  Objet
     */
    public function getObjet()
    {
        return $this->objet;
    }
}
