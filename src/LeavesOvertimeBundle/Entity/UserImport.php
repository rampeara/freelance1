<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * UserImport
 *
 * @ORM\Table(name="axa_user_import")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\UserImportRepository")
 */
class UserImport
{
    public $uploadAbsolutePath;
    
    public function __construct()
    {
        $this->uploadAbsolutePath = sprintf('%s\uploads\user_import', realpath(null));
        //        $this->uploadAbsolutePath = sprintf('%s/uploads/user', $_SERVER['DOCUMENT_ROOT']);
        //        $this->uploadAbsolutePath = __DIR__ . '/../../../web/uploads/user';
    }
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fileName", type="string", length=255, nullable=true)
     */
    private $fileName;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_success", type="boolean", nullable=true)
     */
    private $isSuccess;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="created_by", type="string", length=255, nullable=true, unique=false)
     */
    protected $createdBy;
    
    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return UserImport
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
    
    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    /**
     * Set createdBy.
     *
     * @param string $createdBy
     *
     * @return UserImport
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        
        return $this;
    }
    
    /**
     * Get createdBy.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    
    /**
     * Unmapped property to handle file uploads
     */
    protected $file;
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return UserImport
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }
    
    /**
     * @param bool|null $isSuccess
     *
     * @return UserImport
     */
    public function setIsSuccess(?bool $isSuccess): UserImport {
        $this->isSuccess = $isSuccess;
        return $this;
}
    
    /**
     * @return bool|null
     */
    public function getisSuccess(): ?bool {
        return $this->isSuccess;
    }
    
    public function __toString() {
        return !empty($this->fileName) ? $this->fileName : '';
    }
}
