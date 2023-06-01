<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class BranchService to get WZ2008 codes in german and english
 */
class BranchService
{
    protected array $branches = [
        '00' => [
            'de' => 'undefiniert',
            'default' => 'undefined',
        ],
        '01' => [
            'de' => 'Landwirtschaft, Jagd und damit verbundene Tätigkeiten',
            'default' => 'Agriculture, Hunting, and Related Service Activities',
        ],
        '02' => [
            'de' => 'Forstwirtschaft und Holzeinschlag',
            'default' => 'Forestry and Logging',
        ],
        '03' => [
            'de' => 'Fischerei und Aquakultur',
            'default' => 'Fishing and Aquaculture',
        ],
        '05' => [
            'de' => 'Kohlenbergbau',
            'default' => 'Mining of Coal and Lignite',
        ],
        '06' => [
            'de' => 'Gewinnung von Erdöl und Erdgas',
            'default' => 'Extraction of Crude Petroleum and Natural Gas',
        ],
        '07' => [
            'de' => 'Erzbergbau',
            'default' => 'Mining of Metal Ores',
        ],
        '08' => [
            'de' => 'Gewinnung von Steinen und Erden, sonstiger Bergbau',
            'default' => 'Other Mining and Quarrying',
        ],
        '09' => [
            'de' => 'Erbringung von Dienstleistungen für den Bergbau und für die Gewinnung von Steinen und Erden',
            'default' => 'Mining Support Service Activities',
        ],
        '10' => [
            'de' => 'Herstellung von Nahrungs- und Futtermitteln',
            'default' => 'Manufacture of Food Products',
        ],
        '11' => [
            'de' => 'Getränkeherstellung',
            'default' => 'Manufacture of Beverages',
        ],
        '12' => [
            'de' => 'Tabakverarbeitung',
            'default' => 'Manufacture of Tobacco Products',
        ],
        '13' => [
            'de' => 'Herstellung von Textilien',
            'default' => 'Manufacture of Textiles',
        ],
        '14' => [
            'de' => 'Herstellung von Bekleidung',
            'default' => 'Manufacture of Wearing Apparel',
        ],
        '15' => [
            'de' => 'Herstellung von Leder, Lederwaren und Schuhen',
            'default' => 'Manufacture of Leather and Related Products',
        ],
        '16' => [
            'de' => 'Herstellung von Holz-, Flecht-, Korb- und Korkwaren (ohne Möbel)',
            'default' => 'Manufacture of Wood, Cork, Straw and Plaiting Products (excluding Furniture)',
        ],
        '17' => [
            'de' => 'Herstellung von Papier, Pappe und Waren daraus',
            'default' => 'Manufacture of Paper and Paper Products',
        ],
        '18' => [
            'de' => 'Herstellung von Druckerzeugnissen; Vervielfältigung von bespielten Ton-, Bild- und Datenträgern',
            'default' => 'Printing and Reproduction of Recorded Media',
        ],
        '19' => [
            'de' => 'Kokerei und Mineralölverarbeitung',
            'default' => 'Coke and Refined Petroleum Products Manufacturing',
        ],
        '20' => [
            'de' => 'Herstellung von chemischen Erzeugnissen',
            'default' => 'Manufacture of Chemicals and Chemical Products',
        ],
        '21' => [
            'de' => 'Herstellung von pharmazeutischen Erzeugnissen',
            'default' => 'Manufacture of Pharmaceutical Products',
        ],
        '22' => [
            'de' => 'Herstellung von Gummi- und Kunststoffwaren',
            'default' => 'Manufacture of Rubber and Plastic Products',
        ],
        '23' => [
            'de' => 'Herstellung von Glas und Glaswaren, Keramik, Verarbeitung von Steinen und Erden',
            'default' => 'Manufacture of Glass and Glass Products, Ceramics, and Stone Processing',
        ],
        '24' => [
            'de' => 'Metallerzeugung und -bearbeitung',
            'default' => 'Manufacture of Basic Metals and Fabricated Metal Products',
        ],
        '25' => [
            'de' => 'Herstellung von Metallerzeugnissen',
            'default' => 'Manufacture of Fabricated Metal Products, Except Machinery and Equipment',
        ],
        '26' => [
            'de' => 'Herstellung von Datenverarbeitungsgeräten, elektronischen und optischen Erzeugnissen',
            'default' => 'Manufacture of Computer, Electronic and Optical Products',
        ],
        '27' => [
            'de' => 'Herstellung von elektrischen Ausrüstungen',
            'default' => 'Manufacture of Electrical Equipment',
        ],
        '28' => [
            'de' => 'Maschinenbau',
            'default' => 'Manufacture of Machinery and Equipment',
        ],
        '29' => [
            'de' => 'Herstellung von Kraftwagen und Kraftwagenteilen',
            'default' => 'Manufacture of Motor Vehicles, Trailers, and Semi-Trailers',
        ],
        '30' => [
            'de' => 'Sonstiger Fahrzeugbau',
            'default' => 'Manufacture of Other Transport Equipment',
        ],
        '31' => [
            'de' => 'Herstellung von Möbeln',
            'default' => 'Manufacture of Furniture',
        ],
        '32' => [
            'de' => 'Herstellung von sonstigen Waren',
            'default' => 'Other Manufacturing',
        ],
        '33' => [
            'de' => 'Reparatur und Installation von Maschinen und Ausrüstungen',
            'default' => 'Repair and Installation of Machinery and Equipment',
        ],
        '35' => [
            'de' => 'Energieversorgung',
            'default' => 'Electricity, Gas, Steam, and Air Conditioning Supply',
        ],
        '36' => [
            'de' => 'Wasserversorgung',
            'default' => 'Water Supply',
        ],
        '37' => [
            'de' => 'Abwasserentsorgung',
            'default' => 'Sewerage',
        ],
        '38' => [
            'de' => 'Sammlung, Behandlung und Beseitigung von Abfällen; Rückgewinnung',
            'default' => 'Waste Collection, Treatment and Disposal, and Material Recovery',
        ],
        '39' => [
            'de' => 'Beseitigung von Umweltverschmutzungen und sonstige Entsorgung',
            'default' => 'Remediation Activities and Other Waste Management Services',
        ],
        '41' => [
            'de' => 'Hochbau',
            'default' => 'Construction of Buildings',
        ],
        '42' => [
            'de' => 'Tiefbau',
            'default' => 'Civil Engineering',
        ],
        '43' => [
            'de' => 'Vorbereitende Baustellenarbeiten, Bauinstallation und sonstiges Ausbaugewerbe',
            'default' => 'Specialized Construction Activities',
        ],
        '45' => [
            'de' => 'Handel mit Kraftfahrzeugen; Instandhaltung und Reparatur von Kraftfahrzeugen',
            'default' => 'Wholesale and Retail Trade and Repair of Motor Vehicles and Motorcycles',
        ],
        '46' => [
            'de' => 'Großhandel (ohne Handel mit Kraftfahrzeugen)',
            'default' => 'Wholesale Trade, Except of Motor Vehicles and Motorcycles',
        ],
        '47' => [
            'de' => 'Einzelhandel (ohne Handel mit Kraftfahrzeugen)',
            'default' => 'Retail Trade, Except of Motor Vehicles and Motorcycles',
        ],
        '49' => [
            'de' => 'Landverkehr und Transport in Rohrfernleitungen',
            'default' => 'Land Transport and Transport via Pipelines',
        ],
        '50' => [
            'de' => 'Schifffahrt',
            'default' => 'Water Transport',
        ],
        '51' => [
            'de' => 'Luftfahrt',
            'default' => 'Air Transport',
        ],
        '52' => [
            'de' => 'Lagerei sowie Erbringung von sonstigen Dienstleistungen für den Verkehr',
            'default' => 'Warehousing and Support Activities for Transportation',
        ],
        '53' => [
            'de' => 'Post-, Kurier- und Expressdienste',
            'default' => 'Postal and Courier Activities',
        ],
        '55' => [
            'de' => 'Beherbergung',
            'default' => 'Accommodation',
        ],
        '56' => [
            'de' => 'Gastronomie',
            'default' => 'Food and Beverage Service Activities',
        ],
        '58' => [
            'de' => 'Verlagswesen',
            'default' => 'Publishing Activities',
        ],
        '59' => [
            'de' => 'Herstellung, Verleih und Vertrieb von Filmen und Fernsehprogrammen; Kinos; Tonstudios und Verlegen von Musik',
            'default' => 'Motion Picture, Video and Television Program Production, Sound Recording and Music Publishing Activities',
        ],
        '60' => [
            'de' => 'Rundfunkveranstalter',
            'default' => 'Programming and Broadcasting Activities',
        ],
        '61' => [
            'de' => 'Telekommunikation',
            'default' => 'Telecommunications',
        ],
        '62' => [
            'de' => 'Erbringung von Dienstleistungen der Informationstechnologie',
            'default' => 'Computer Programming, Consultancy and Related Activities',
        ],
        '63' => [
            'de' => 'Informationsdienstleistungen',
            'default' => 'Information Service Activities',
        ],
        '64' => [
            'de' => 'Erbringung von Finanzdienstleistungen',
            'default' => 'Financial Services',
        ],
        '65' => [
            'de' => 'Versicherungen, Rückversicherungen und Pensionskassen (ohne Sozialversicherung)',
            'default' => 'Insurance, Reinsurance and Pension Funding, Except Compulsory Social Security',
        ],
        '66' => [
            'de' => 'Mit Finanz- und Versicherungsdienstleistungen verbundene Tätigkeiten',
            'default' => 'Activities Auxiliary to Financial Services and Insurance Activities',
        ],
        '68' => [
            'de' => 'Grundstücks- und Wohnungswesen',
            'default' => 'Real Estate Activities',
        ],
        '69' => [
            'de' => 'Rechts- und Steuerberatung, Wirtschaftsprüfung',
            'default' => 'Legal and Accounting Activities',
        ],
        '70' => [
            'de' => 'Verwaltung und Führung von Unternehmen und Betrieben; Unternehmensberatung',
            'default' => 'Activities of Head Offices; Management Consultancy Activities',
        ],
        '71' => [
            'de' => 'Architektur- und Ingenieurbüros; technische, physikalische und chemische Untersuchung',
            'default' => 'Architectural and Engineering Activities; Technical Testing and Analysis',
        ],
        '72' => [
            'de' => 'Forschung und Entwicklung',
            'default' => 'Scientific Research and Development',
        ],
        '73' => [
            'de' => 'Werbung und Marktforschung',
            'default' => 'Advertising and Market Research',
        ],
        '74' => [
            'de' => 'Sonstige freiberufliche, wissenschaftliche und technische Tätigkeiten',
            'default' => 'Other Professional, Scientific and Technical Activities',
        ],
        '75' => [
            'de' => 'Veterinärwesen',
            'default' => 'Veterinary Activities',
        ],
        '77' => [
            'de' => 'Vermietung von beweglichen Sachen',
            'default' => 'Rental and Leasing Activities',
        ],
        '78' => [
            'de' => 'Vermittlung und Überlassung von Arbeitskräften',
            'default' => 'Employment Activities',
        ],
        '79' => [
            'de' => 'Reisebüros, Reiseveranstalter und Erbringung sonstiger Reservierungsdienstleistungen',
            'default' => 'Travel Agency, Tour Operator and Other Reservation Service and Related Activities',
        ],
        '80' => [
            'de' => 'Wach- und Sicherheitsdienste sowie Detekteien',
            'default' => 'Security and Investigation Activities',
        ],
        '81' => [
            'de' => 'Gebäudebetreuung; Garten- und Landschaftsbau',
            'default' => 'Services to Buildings and Landscape Activities',
        ],
        '82' => [
            'de' => 'Erbringung von wirtschaftlichen Dienstleistungen für Unternehmen und Privatpersonen a. n. g.',
            'default' => 'Office Administrative, Office Support and Other Business Support Activities',
        ],
        '84' => [
            'de' => 'Öffentliche Verwaltung, Verteidigung; Sozialversicherung',
            'default' => 'Public Administration and Defence; Compulsory Social Security',
        ],
        '85' => [
            'de' => 'Erziehung und Unterricht',
            'default' => 'Education',
        ],
        '86' => [
            'de' => 'Gesundheitswesen',
            'default' => 'Human Health Activities',
        ],
        '87' => [
            'de' => 'Heime (ohne Erholungs- und Ferienheime)',
            'default' => 'Residential Care Activities',
        ],
        '88' => [
            'de' => 'Sozialwesen (ohne Heime)',
            'default' => 'Social Work Activities without Accommodation',
        ],
        '90' => [
            'de' => 'Kreative, künstlerische und unterhaltende Tätigkeiten',
            'default' => 'Creative, Arts and Entertainment Activities',
        ],
        '91' => [
            'de' => 'Bibliotheken, Archive, Museen, botanische und zoologische Gärten',
            'default' => 'Libraries, Archives, Museums and other Cultural Activities',
        ],
        '92' => [
            'de' => 'Spiel-, Wett- und Lotteriewesen',
            'default' => 'Gambling and Betting Activities',
        ],
        '93' => [
            'de' => 'Erbringung von Dienstleistungen des Sports, der Unterhaltung und der Erholung',
            'default' => 'Sports Activities and Amusement and Recreation Activities',
        ],
        '94' => [
            'de' => 'Interessenvertretungen sowie kirchliche und sonstige religiöse Vereinigungen (ohne Sozialwesen und Sport)',
            'default' => 'Activities of Membership Organizations, Not Elsewhere Classified',
        ],
        '95' => [
            'de' => 'Reparatur von Datenverarbeitungsgeräten und Gebrauchsgütern',
            'default' => 'Repair of Computers and Personal and Household Goods',
        ],
        '96' => [
            'de' => 'Erbringung von sonstigen überwiegend persönlichen Dienstleistungen',
            'default' => 'Other Personal Service Activities',
        ],
        '97' => [
            'de' => 'Private Haushalte mit Hauspersonal',
            'default' => 'Activities of Households as Employers of Domestic Personnel',
        ],
        '98' => [
            'de' => 'Herstellung von Waren und Erbringung von Dienstleistungen durch private Haushalte für den Eigenbedarf ohne ausgeprägten Schwerpunkt',
            'default' => 'Undifferentiated Goods- and Services-Producing Activities of Private Households for Own Use',
        ],
        '99' => [
            'de' => 'Exterritoriale Organisationen und Körperschaften',
            'default' => 'Activities of Extraterritorial Organizations and Bodies',
        ],
    ];

    public function getMainBranchCodeFromAnyCode(string $code): string
    {
        if (!empty($code)) {
            return substr($code, 0, 2);
        }
        return '';
    }

    public function getBranchNameByCode(string $code): string
    {
        return $this->getBranchNameByCodeForLanguage($code, $this->getLanguageKey());
    }

    public function getBranchNameByCodeForDefaultLanguage(string $code): string
    {
        return $this->getBranchNameByCodeForLanguage($code, 'en');
    }

    public function getBranchNameByCodeForGermanLanguage(string $code): string
    {
        return $this->getBranchNameByCodeForLanguage($code, 'de');
    }

    protected function getBranchNameByCodeForLanguage(string $code, string $language): string
    {
        if (array_key_exists($code, $this->branches)) {
            return $this->branches[$code][$language];
        }
        return $this->branches['00'][$language];
    }

    protected function getLanguageKey(): string
    {
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            // Frontend application
            $siteLanguage = $this->getCurrentSiteLanguage();

            // Get values from site language
            if ($siteLanguage !== null) {
                return $siteLanguage->getTypo3Language();
            }
        } elseif (!empty($GLOBALS['BE_USER']->user['lang'])) {
            return $GLOBALS['BE_USER']->user['lang'];
        }
        return 'default';
    }

    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            return $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        }
        return null;
    }
}
