<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Factory;

use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Repository\CompanyRepository;
use In2code\Lux\Exception\ConfigurationException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class CompanyFactory
{
    protected CompanyRepository $companyRepository;
    protected DataMapper $dataMapper;

    public function __construct(CompanyRepository $companyRepository, DataMapper $dataMapper)
    {
        $this->companyRepository = $companyRepository;
        $this->dataMapper = $dataMapper;
    }

    /**
     * @param array $properties
     * @return Company
     * @throws ConfigurationException
     * @throws IllegalObjectTypeException
     */
    public function getExistingOrNewPersistedCompany(array $properties): Company
    {
        if (($properties['name'] ?? '') === '') {
            throw new ConfigurationException('$properties["name"] must not be empty', 1684437981);
        }
        $properties['title'] = $properties['name']; // map title

        $company = $this->companyRepository->findByTitleAndDomain($properties['title'], $properties['domain'] ?? '');
        if ($company === null) {
            $company = $this->getNewCompany($properties);
        }
        return $company;
    }

    /**
     * @param array $properties
     * @return Company
     * @throws IllegalObjectTypeException
     */
    protected function getNewCompany(array $properties): Company
    {
        /**
         * Set a value against missing array key error. Also take a unique value against caching issues when called more
         * than only one time. Even creating new instances of datamapper everytime when called seems not to help here.
         */
        $properties['uid'] = md5(serialize($properties));
        $properties['contacts'] = json_encode($properties['contacts'] ?? '');

        /** @var Company $company */
        $company = $this->dataMapper->map(Company::class, [$properties])[0];
        $company->_setProperty('uid', null); // reset uid to null value to be able to store a new record
        $company->_memorizeCleanState(); // tell datamapper a new record to be able to store a new record

        $this->companyRepository->add($company);
        $this->companyRepository->persistAll();

        return $company;
    }
}
