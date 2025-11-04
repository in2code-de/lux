<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service;

use LogicException;

class CountryService
{
    protected array $countries = [
        'AD' => [
            'name' => 'Andorra',
            'alpha2' => 'AD',
            'alpha3' => 'AND',
        ],
        'AE' => [
            'name' => 'United Arab Emirates',
            'alpha2' => 'AE',
            'alpha3' => 'ARE',
        ],
        'AF' => [
            'name' => 'Afghanistan',
            'alpha2' => 'AF',
            'alpha3' => 'AFG',
        ],
        'AG' => [
            'name' => 'Antigua and Barbuda',
            'alpha2' => 'AG',
            'alpha3' => 'ATG',
        ],
        'AI' => [
            'name' => 'Anguilla',
            'alpha2' => 'AI',
            'alpha3' => 'AIA',
        ],
        'AL' => [
            'name' => 'Albania',
            'alpha2' => 'AL',
            'alpha3' => 'ALB',
        ],
        'AM' => [
            'name' => 'Armenia',
            'alpha2' => 'AM',
            'alpha3' => 'ARM',
        ],
        'AO' => [
            'name' => 'Angola',
            'alpha2' => 'AO',
            'alpha3' => 'AGO',
        ],
        'AQ' => [
            'name' => 'Antarctica',
            'alpha2' => 'AQ',
            'alpha3' => 'ATA',
        ],
        'AR' => [
            'name' => 'Argentina',
            'alpha2' => 'AR',
            'alpha3' => 'ARG',
        ],
        'AS' => [
            'name' => 'American Samoa',
            'alpha2' => 'AS',
            'alpha3' => 'ASM',
        ],
        'AT' => [
            'name' => 'Austria',
            'alpha2' => 'AT',
            'alpha3' => 'AUT',
        ],
        'AU' => [
            'name' => 'Australia',
            'alpha2' => 'AU',
            'alpha3' => 'AUS',
        ],
        'AW' => [
            'name' => 'Aruba',
            'alpha2' => 'AW',
            'alpha3' => 'ABW',
        ],
        'AZ' => [
            'name' => 'Azerbaijan',
            'alpha2' => 'AZ',
            'alpha3' => 'AZE',
        ],
        'BA' => [
            'name' => 'Bosnia and Herzegovina',
            'alpha2' => 'BA',
            'alpha3' => 'BIH',
        ],
        'BB' => [
            'name' => 'Barbados',
            'alpha2' => 'BB',
            'alpha3' => 'BRB',
        ],
        'BD' => [
            'name' => 'Bangladesh',
            'alpha2' => 'BD',
            'alpha3' => 'BGD',
        ],
        'BE' => [
            'name' => 'Belgium',
            'alpha2' => 'BE',
            'alpha3' => 'BEL',
        ],
        'BF' => [
            'name' => 'Burkina Faso',
            'alpha2' => 'BF',
            'alpha3' => 'BFA',
        ],
        'BG' => [
            'name' => 'Bulgaria',
            'alpha2' => 'BG',
            'alpha3' => 'BGR',
        ],
        'BH' => [
            'name' => 'Bahrain',
            'alpha2' => 'BH',
            'alpha3' => 'BHR',
        ],
        'BI' => [
            'name' => 'Burundi',
            'alpha2' => 'BI',
            'alpha3' => 'BDI',
        ],
        'BJ' => [
            'name' => 'Benin',
            'alpha2' => 'BJ',
            'alpha3' => 'BEN',
        ],
        'BL' => [
            'name' => 'Saint Barthélemy',
            'alpha2' => 'BL',
            'alpha3' => 'BLM',
        ],
        'BM' => [
            'name' => 'Bermuda',
            'alpha2' => 'BM',
            'alpha3' => 'BMU',
        ],
        'BN' => [
            'name' => 'Brunei Darussalam',
            'alpha2' => 'BN',
            'alpha3' => 'BRN',
        ],
        'BO' => [
            'name' => 'Bolivia (Plurinational State of)',
            'alpha2' => 'BO',
            'alpha3' => 'BOL',
        ],
        'BQ' => [
            'name' => 'Bonaire, Sint Eustatius and Saba',
            'alpha2' => 'BQ',
            'alpha3' => 'BES',
        ],
        'BR' => [
            'name' => 'Brazil',
            'alpha2' => 'BR',
            'alpha3' => 'BRA',
        ],
        'BS' => [
            'name' => 'Bahamas',
            'alpha2' => 'BS',
            'alpha3' => 'BHS',
        ],
        'BT' => [
            'name' => 'Bhutan',
            'alpha2' => 'BT',
            'alpha3' => 'BTN',
        ],
        'BV' => [
            'name' => 'Bouvet Island',
            'alpha2' => 'BV',
            'alpha3' => 'BVT',
        ],
        'BW' => [
            'name' => 'Botswana',
            'alpha2' => 'BW',
            'alpha3' => 'BWA',
        ],
        'BY' => [
            'name' => 'Belarus',
            'alpha2' => 'BY',
            'alpha3' => 'BLR',
        ],
        'BZ' => [
            'name' => 'Belize',
            'alpha2' => 'BZ',
            'alpha3' => 'BLZ',
        ],
        'CA' => [
            'name' => 'Canada',
            'alpha2' => 'CA',
            'alpha3' => 'CAN',
        ],
        'CC' => [
            'name' => 'Cocos (Keeling) Islands',
            'alpha2' => 'CC',
            'alpha3' => 'CCK',
        ],
        'CD' => [
            'name' => 'Congo (Democratic Republic of the)',
            'alpha2' => 'CD',
            'alpha3' => 'COD',
        ],
        'CF' => [
            'name' => 'Central African Republic',
            'alpha2' => 'CF',
            'alpha3' => 'CAF',
        ],
        'CG' => [
            'name' => 'Congo',
            'alpha2' => 'CG',
            'alpha3' => 'COG',
        ],
        'CH' => [
            'name' => 'Switzerland',
            'alpha2' => 'CH',
            'alpha3' => 'CHE',
        ],
        'CI' => [
            'name' => "Côte d'Ivoire",
            'alpha2' => 'CI',
            'alpha3' => 'CIV',
        ],
        'CK' => [
            'name' => 'Cook Islands',
            'alpha2' => 'CK',
            'alpha3' => 'COK',
        ],
        'CL' => [
            'name' => 'Chile',
            'alpha2' => 'CL',
            'alpha3' => 'CHL',
        ],
        'CM' => [
            'name' => 'Cameroon',
            'alpha2' => 'CM',
            'alpha3' => 'CMR',
        ],
        'CN' => [
            'name' => 'China',
            'alpha2' => 'CN',
            'alpha3' => 'CHN',
        ],
        'CO' => [
            'name' => 'Colombia',
            'alpha2' => 'CO',
            'alpha3' => 'COL',
        ],
        'CR' => [
            'name' => 'Costa Rica',
            'alpha2' => 'CR',
            'alpha3' => 'CRI',
        ],
        'CU' => [
            'name' => 'Cuba',
            'alpha2' => 'CU',
            'alpha3' => 'CUB',
        ],
        'CV' => [
            'name' => 'Cabo Verde',
            'alpha2' => 'CV',
            'alpha3' => 'CPV',
        ],
        'CW' => [
            'name' => 'Curaçao',
            'alpha2' => 'CW',
            'alpha3' => 'CUW',
        ],
        'CX' => [
            'name' => 'Christmas Island',
            'alpha2' => 'CX',
            'alpha3' => 'CXR',
        ],
        'CY' => [
            'name' => 'Cyprus',
            'alpha2' => 'CY',
            'alpha3' => 'CYP',
        ],
        'CZ' => [
            'name' => 'Czech Republic',
            'alpha2' => 'CZ',
            'alpha3' => 'CZE',
        ],
        'DE' => [
            'name' => 'Germany',
            'alpha2' => 'DE',
            'alpha3' => 'DEU',
        ],
        'DJ' => [
            'name' => 'Djibouti',
            'alpha2' => 'DJ',
            'alpha3' => 'DJI',
        ],
        'DK' => [
            'name' => 'Denmark',
            'alpha2' => 'DK',
            'alpha3' => 'DNK',
        ],
        'DM' => [
            'name' => 'Dominica',
            'alpha2' => 'DM',
            'alpha3' => 'DMA',
        ],
        'DO' => [
            'name' => 'Dominican Republic',
            'alpha2' => 'DO',
            'alpha3' => 'DOM',
        ],
        'DZ' => [
            'name' => 'Algeria',
            'alpha2' => 'DZ',
            'alpha3' => 'DZA',
        ],
        'EC' => [
            'name' => 'Ecuador',
            'alpha2' => 'EC',
            'alpha3' => 'ECU',
        ],
        'EE' => [
            'name' => 'Estonia',
            'alpha2' => 'EE',
            'alpha3' => 'EST',
        ],
        'EG' => [
            'name' => 'Egypt',
            'alpha2' => 'EG',
            'alpha3' => 'EGY',
        ],
        'EH' => [
            'name' => 'Western Sahara',
            'alpha2' => 'EH',
            'alpha3' => 'ESH',
        ],
        'ER' => [
            'name' => 'Eritrea',
            'alpha2' => 'ER',
            'alpha3' => 'ERI',
        ],
        'ES' => [
            'name' => 'Spain',
            'alpha2' => 'ES',
            'alpha3' => 'ESP',
        ],
        'ET' => [
            'name' => 'Ethiopia',
            'alpha2' => 'ET',
            'alpha3' => 'ETH',
        ],
        'FI' => [
            'name' => 'Finland',
            'alpha2' => 'FI',
            'alpha3' => 'FIN',
        ],
        'FJ' => [
            'name' => 'Fiji',
            'alpha2' => 'FJ',
            'alpha3' => 'FJI',
        ],
        'FK' => [
            'name' => 'Falkland Islands (Malvinas)',
            'alpha2' => 'FK',
            'alpha3' => 'FLK',
        ],
        'FM' => [
            'name' => 'Micronesia,
            Federated States of',
            'alpha2' => 'FM',
            'alpha3' => 'FSM',
        ],
        'FO' => [
            'name' => 'Faroe Islands',
            'alpha2' => 'FO',
            'alpha3' => 'FRO',
        ],
        'FR' => [
            'name' => 'France',
            'alpha2' => 'FR',
            'alpha3' => 'FRA',
        ],
        'GA' => [
            'name' => 'Gabon',
            'alpha2' => 'GA',
            'alpha3' => 'GAB',
        ],
        'GB' => [
            'name' => 'United Kingdom of Great Britain and Northern Ireland',
            'alpha2' => 'GB',
            'alpha3' => 'GBR',
        ],
        'GD' => [
            'name' => 'Grenada',
            'alpha2' => 'GD',
            'alpha3' => 'GRD',
        ],
        'GE' => [
            'name' => 'Georgia',
            'alpha2' => 'GE',
            'alpha3' => 'GEO',
        ],
        'GF' => [
            'name' => 'French Guiana',
            'alpha2' => 'GF',
            'alpha3' => 'GUF',
        ],
        'GG' => [
            'name' => 'Guernsey',
            'alpha2' => 'GG',
            'alpha3' => 'GGY',
        ],
        'GH' => [
            'name' => 'Ghana',
            'alpha2' => 'GH',
            'alpha3' => 'GHA',
        ],
        'GI' => [
            'name' => 'Gibraltar',
            'alpha2' => 'GI',
            'alpha3' => 'GIB',
        ],
        'GL' => [
            'name' => 'Greenland',
            'alpha2' => 'GL',
            'alpha3' => 'GRL',
        ],
        'GM' => [
            'name' => 'Gambia',
            'alpha2' => 'GM',
            'alpha3' => 'GMB',
        ],
        'GN' => [
            'name' => 'Guinea',
            'alpha2' => 'GN',
            'alpha3' => 'GIN',
        ],
        'GP' => [
            'name' => 'Guadeloupe',
            'alpha2' => 'GP',
            'alpha3' => 'GLP',
        ],
        'GQ' => [
            'name' => 'Equatorial Guinea',
            'alpha2' => 'GQ',
            'alpha3' => 'GNQ',
        ],
        'GR' => [
            'name' => 'Greece',
            'alpha2' => 'GR',
            'alpha3' => 'GRC',
        ],
        'GS' => [
            'name' => 'South Georgia and the South Sandwich Islands',
            'alpha2' => 'GS',
            'alpha3' => 'SGS',
        ],
        'GT' => [
            'name' => 'Guatemala',
            'alpha2' => 'GT',
            'alpha3' => 'GTM',
        ],
        'GU' => [
            'name' => 'Guam',
            'alpha2' => 'GU',
            'alpha3' => 'GUM',
        ],
        'GW' => [
            'name' => 'Guinea-Bissau',
            'alpha2' => 'GW',
            'alpha3' => 'GNB',
        ],
        'GY' => [
            'name' => 'Guyana',
            'alpha2' => 'GY',
            'alpha3' => 'GUY',
        ],
        'HK' => [
            'name' => 'Hong Kong',
            'alpha2' => 'HK',
            'alpha3' => 'HKG',
        ],
        'HM' => [
            'name' => 'Heard Island and McDonald Islands',
            'alpha2' => 'HM',
            'alpha3' => 'HMD',
        ],
        'HN' => [
            'name' => 'Honduras',
            'alpha2' => 'HN',
            'alpha3' => 'HND',
        ],
        'HR' => [
            'name' => 'Croatia',
            'alpha2' => 'HR',
            'alpha3' => 'HRV',
        ],
        'HT' => [
            'name' => 'Haiti',
            'alpha2' => 'HT',
            'alpha3' => 'HTI',
        ],
        'HU' => [
            'name' => 'Hungary',
            'alpha2' => 'HU',
            'alpha3' => 'HUN',
        ],
        'ID' => [
            'name' => 'Indonesia',
            'alpha2' => 'ID',
            'alpha3' => 'IDN',
        ],
        'IE' => [
            'name' => 'Ireland',
            'alpha2' => 'IE',
            'alpha3' => 'IRL',
        ],
        'IL' => [
            'name' => 'Israel',
            'alpha2' => 'IL',
            'alpha3' => 'ISR',
        ],
        'IM' => [
            'name' => 'Isle of Man',
            'alpha2' => 'IM',
            'alpha3' => 'IMN',
        ],
        'IN' => [
            'name' => 'India',
            'alpha2' => 'IN',
            'alpha3' => 'IND',
        ],
        'IO' => [
            'name' => 'British Indian Ocean Territory',
            'alpha2' => 'IO',
            'alpha3' => 'IOT',
        ],
        'IQ' => [
            'name' => 'Iraq',
            'alpha2' => 'IQ',
            'alpha3' => 'IRQ',
        ],
        'IR' => [
            'name' => 'Iran,
            Islamic Republic of',
            'alpha2' => 'IR',
            'alpha3' => 'IRN',
        ],
        'IS' => [
            'name' => 'Iceland',
            'alpha2' => 'IS',
            'alpha3' => 'ISL',
        ],
        'IT' => [
            'name' => 'Italy',
            'alpha2' => 'IT',
            'alpha3' => 'ITA',
        ],
        'JE' => [
            'name' => 'Jersey',
            'alpha2' => 'JE',
            'alpha3' => 'JEY',
        ],
        'JM' => [
            'name' => 'Jamaica',
            'alpha2' => 'JM',
            'alpha3' => 'JAM',
        ],
        'JO' => [
            'name' => 'Jordan',
            'alpha2' => 'JO',
            'alpha3' => 'JOR',
        ],
        'JP' => [
            'name' => 'Japan',
            'alpha2' => 'JP',
            'alpha3' => 'JPN',
        ],
        'KE' => [
            'name' => 'Kenya',
            'alpha2' => 'KE',
            'alpha3' => 'KEN',
        ],
        'KG' => [
            'name' => 'Kyrgyzstan',
            'alpha2' => 'KG',
            'alpha3' => 'KGZ',
        ],
        'KH' => [
            'name' => 'Cambodia',
            'alpha2' => 'KH',
            'alpha3' => 'KHM',
        ],
        'KI' => [
            'name' => 'Kiribati',
            'alpha2' => 'KI',
            'alpha3' => 'KIR',
        ],
        'KM' => [
            'name' => 'Comoros',
            'alpha2' => 'KM',
            'alpha3' => 'COM',
        ],
        'KN' => [
            'name' => 'Saint Kitts and Nevis',
            'alpha2' => 'KN',
            'alpha3' => 'KNA',
        ],
        'KP' => [
            'name' => "Korea,
            Democratic People's Republic of",
            'alpha2' => 'KP',
            'alpha3' => 'PRK',
        ],
        'KR' => [
            'name' => 'Korea,
            Republic of',
            'alpha2' => 'KR',
            'alpha3' => 'KOR',
        ],
        'KW' => [
            'name' => 'Kuwait',
            'alpha2' => 'KW',
            'alpha3' => 'KWT',
        ],
        'KY' => [
            'name' => 'Cayman Islands',
            'alpha2' => 'KY',
            'alpha3' => 'CYM',
        ],
        'KZ' => [
            'name' => 'Kazakhstan',
            'alpha2' => 'KZ',
            'alpha3' => 'KAZ',
        ],
        'LA' => [
            'name' => "Lao People's Democratic Republic",
            'alpha2' => 'LA',
            'alpha3' => 'LAO',
        ],
        'LB' => [
            'name' => 'Lebanon',
            'alpha2' => 'LB',
            'alpha3' => 'LBN',
        ],
        'LC' => [
            'name' => 'Saint Lucia',
            'alpha2' => 'LC',
            'alpha3' => 'LCA',
        ],
        'LI' => [
            'name' => 'Liechtenstein',
            'alpha2' => 'LI',
            'alpha3' => 'LIE',
        ],
        'LK' => [
            'name' => 'Sri Lanka',
            'alpha2' => 'LK',
            'alpha3' => 'LKA',
        ],
        'LR' => [
            'name' => 'Liberia',
            'alpha2' => 'LR',
            'alpha3' => 'LBR',
        ],
        'LS' => [
            'name' => 'Lesotho',
            'alpha2' => 'LS',
            'alpha3' => 'LSO',
        ],
        'LT' => [
            'name' => 'Lithuania',
            'alpha2' => 'LT',
            'alpha3' => 'LTU',
        ],
        'LU' => [
            'name' => 'Luxembourg',
            'alpha2' => 'LU',
            'alpha3' => 'LUX',
        ],
        'LV' => [
            'name' => 'Latvia',
            'alpha2' => 'LV',
            'alpha3' => 'LVA',
        ],
        'LY' => [
            'name' => 'Libya',
            'alpha2' => 'LY',
            'alpha3' => 'LBY',
        ],
        'MA' => [
            'name' => 'Morocco',
            'alpha2' => 'MA',
            'alpha3' => 'MAR',
        ],
        'MC' => [
            'name' => 'Monaco',
            'alpha2' => 'MC',
            'alpha3' => 'MCO',
        ],
        'MD' => [
            'name' => 'Moldova,
            Republic of',
            'alpha2' => 'MD',
            'alpha3' => 'MDA',
        ],
        'ME' => [
            'name' => 'Montenegro',
            'alpha2' => 'ME',
            'alpha3' => 'MNE',
        ],
        'MF' => [
            'name' => 'Saint Martin (French part)',
            'alpha2' => 'MF',
            'alpha3' => 'MAF',
        ],
        'MG' => [
            'name' => 'Madagascar',
            'alpha2' => 'MG',
            'alpha3' => 'MDG',
        ],
        'MH' => [
            'name' => 'Marshall Islands',
            'alpha2' => 'MH',
            'alpha3' => 'MHL',
        ],
        'MK' => [
            'name' => 'North Macedonia',
            'alpha2' => 'MK',
            'alpha3' => 'MKD',
        ],
        'ML' => [
            'name' => 'Mali',
            'alpha2' => 'ML',
            'alpha3' => 'MLI',
        ],
        'MM' => [
            'name' => 'Myanmar',
            'alpha2' => 'MM',
            'alpha3' => 'MMR',
        ],
        'MN' => [
            'name' => 'Mongolia',
            'alpha2' => 'MN',
            'alpha3' => 'MNG',
        ],
        'MO' => [
            'name' => 'Macao',
            'alpha2' => 'MO',
            'alpha3' => 'MAC',
        ],
        'MP' => [
            'name' => 'Northern Mariana Islands',
            'alpha2' => 'MP',
            'alpha3' => 'MNP',
        ],
        'MQ' => [
            'name' => 'Martinique',
            'alpha2' => 'MQ',
            'alpha3' => 'MTQ',
        ],
        'MR' => [
            'name' => 'Mauritania',
            'alpha2' => 'MR',
            'alpha3' => 'MRT',
        ],
        'MS' => [
            'name' => 'Montserrat',
            'alpha2' => 'MS',
            'alpha3' => 'MSR',
        ],
        'MT' => [
            'name' => 'Malta',
            'alpha2' => 'MT',
            'alpha3' => 'MLT',
        ],
        'MU' => [
            'name' => 'Mauritius',
            'alpha2' => 'MU',
            'alpha3' => 'MUS',
        ],
        'MV' => [
            'name' => 'Maldives',
            'alpha2' => 'MV',
            'alpha3' => 'MDV',
        ],
        'MW' => [
            'name' => 'Malawi',
            'alpha2' => 'MW',
            'alpha3' => 'MWI',
        ],
        'MX' => [
            'name' => 'Mexico',
            'alpha2' => 'MX',
            'alpha3' => 'MEX',
        ],
        'MY' => [
            'name' => 'Malaysia',
            'alpha2' => 'MY',
            'alpha3' => 'MYS',
        ],
        'MZ' => [
            'name' => 'Mozambique',
            'alpha2' => 'MZ',
            'alpha3' => 'MOZ',
        ],
        'NA' => [
            'name' => 'Namibia',
            'alpha2' => 'NA',
            'alpha3' => 'NAM',
        ],
        'NC' => [
            'name' => 'New Caledonia',
            'alpha2' => 'NC',
            'alpha3' => 'NCL',
        ],
        'NE' => [
            'name' => 'Niger',
            'alpha2' => 'NE',
            'alpha3' => 'NER',
        ],
        'NF' => [
            'name' => 'Norfolk Island',
            'alpha2' => 'NF',
            'alpha3' => 'NFK',
        ],
        'NG' => [
            'name' => 'Nigeria',
            'alpha2' => 'NG',
            'alpha3' => 'NGA',
        ],
        'NI' => [
            'name' => 'Nicaragua',
            'alpha2' => 'NI',
            'alpha3' => 'NIC',
        ],
        'NL' => [
            'name' => 'Netherlands',
            'alpha2' => 'NL',
            'alpha3' => 'NLD',
        ],
        'NO' => [
            'name' => 'Norway',
            'alpha2' => 'NO',
            'alpha3' => 'NOR',
        ],
        'NP' => [
            'name' => 'Nepal',
            'alpha2' => 'NP',
            'alpha3' => 'NPL',
        ],
        'NR' => [
            'name' => 'Nauru',
            'alpha2' => 'NR',
            'alpha3' => 'NRU',
        ],
        'NU' => [
            'name' => 'Niue',
            'alpha2' => 'NU',
            'alpha3' => 'NIU',
        ],
        'NZ' => [
            'name' => 'New Zealand',
            'alpha2' => 'NZ',
            'alpha3' => 'NZL',
        ],
        'OM' => [
            'name' => 'Oman',
            'alpha2' => 'OM',
            'alpha3' => 'OMN',
        ],
        'PA' => [
            'name' => 'Panama',
            'alpha2' => 'PA',
            'alpha3' => 'PAN',
        ],
        'PE' => [
            'name' => 'Peru',
            'alpha2' => 'PE',
            'alpha3' => 'PER',
        ],
        'PF' => [
            'name' => 'French Polynesia',
            'alpha2' => 'PF',
            'alpha3' => 'PYF',
        ],
        'PG' => [
            'name' => 'Papua New Guinea',
            'alpha2' => 'PG',
            'alpha3' => 'PNG',
        ],
        'PH' => [
            'name' => 'Philippines',
            'alpha2' => 'PH',
            'alpha3' => 'PHL',
        ],
        'PK' => [
            'name' => 'Pakistan',
            'alpha2' => 'PK',
            'alpha3' => 'PAK',
        ],
        'PL' => [
            'name' => 'Poland',
            'alpha2' => 'PL',
            'alpha3' => 'POL',
        ],
        'PM' => [
            'name' => 'Saint Pierre and Miquelon',
            'alpha2' => 'PM',
            'alpha3' => 'SPM',
        ],
        'PN' => [
            'name' => 'Pitcairn',
            'alpha2' => 'PN',
            'alpha3' => 'PCN',
        ],
        'PR' => [
            'name' => 'Puerto Rico',
            'alpha2' => 'PR',
            'alpha3' => 'PRI',
        ],
        'PS' => [
            'name' => 'Palestine,
            State of',
            'alpha2' => 'PS',
            'alpha3' => 'PSE',
        ],
        'PT' => [
            'name' => 'Portugal',
            'alpha2' => 'PT',
            'alpha3' => 'PRT',
        ],
        'PW' => [
            'name' => 'Palau',
            'alpha2' => 'PW',
            'alpha3' => 'PLW',
        ],
        'PY' => [
            'name' => 'Paraguay',
            'alpha2' => 'PY',
            'alpha3' => 'PRY',
        ],
        'QA' => [
            'name' => 'Qatar',
            'alpha2' => 'QA',
            'alpha3' => 'QAT',
        ],
        'RE' => [
            'name' => 'Réunion',
            'alpha2' => 'RE',
            'alpha3' => 'REU',
        ],
        'RO' => [
            'name' => 'Romania',
            'alpha2' => 'RO',
            'alpha3' => 'ROU',
        ],
        'RS' => [
            'name' => 'Serbia',
            'alpha2' => 'RS',
            'alpha3' => 'SRB',
        ],
        'RU' => [
            'name' => 'Russian Federation',
            'alpha2' => 'RU',
            'alpha3' => 'RUS',
        ],
        'RW' => [
            'name' => 'Rwanda',
            'alpha2' => 'RW',
            'alpha3' => 'RWA',
        ],
        'SA' => [
            'name' => 'Saudi Arabia',
            'alpha2' => 'SA',
            'alpha3' => 'SAU',
        ],
        'SB' => [
            'name' => 'Solomon Islands',
            'alpha2' => 'SB',
            'alpha3' => 'SLB',
        ],
        'SC' => [
            'name' => 'Seychelles',
            'alpha2' => 'SC',
            'alpha3' => 'SYC',
        ],
        'SD' => [
            'name' => 'Sudan',
            'alpha2' => 'SD',
            'alpha3' => 'SDN',
        ],
        'SE' => [
            'name' => 'Sweden',
            'alpha2' => 'SE',
            'alpha3' => 'SWE',
        ],
        'SG' => [
            'name' => 'Singapore',
            'alpha2' => 'SG',
            'alpha3' => 'SGP',
        ],
        'SH' => [
            'name' => 'Saint Helena,
            Ascension and Tristan da Cunha',
            'alpha2' => 'SH',
            'alpha3' => 'SHN',
        ],
        'SI' => [
            'name' => 'Slovenia',
            'alpha2' => 'SI',
            'alpha3' => 'SVN',
        ],
        'SJ' => [
            'name' => 'Svalbard and Jan Mayen',
            'alpha2' => 'SJ',
            'alpha3' => 'SJM',
        ],
        'SK' => [
            'name' => 'Slovakia',
            'alpha2' => 'SK',
            'alpha3' => 'SVK',
        ],
        'SL' => [
            'name' => 'Sierra Leone',
            'alpha2' => 'SL',
            'alpha3' => 'SLE',
        ],
        'SM' => [
            'name' => 'San Marino',
            'alpha2' => 'SM',
            'alpha3' => 'SMR',
        ],
        'SN' => [
            'name' => 'Senegal',
            'alpha2' => 'SN',
            'alpha3' => 'SEN',
        ],
        'SO' => [
            'name' => 'Somalia',
            'alpha2' => 'SO',
            'alpha3' => 'SOM',
        ],
        'SR' => [
            'name' => 'Suriname',
            'alpha2' => 'SR',
            'alpha3' => 'SUR',
        ],
        'SS' => [
            'name' => 'South Sudan',
            'alpha2' => 'SS',
            'alpha3' => 'SSD',
        ],
        'ST' => [
            'name' => 'Sao Tome and Principe',
            'alpha2' => 'ST',
            'alpha3' => 'STP',
        ],
        'SV' => [
            'name' => 'El Salvador',
            'alpha2' => 'SV',
            'alpha3' => 'SLV',
        ],
        'SX' => [
            'name' => 'Sint Maarten (Dutch part)',
            'alpha2' => 'SX',
            'alpha3' => 'SXM',
        ],
        'SY' => [
            'name' => 'Syrian Arab Republic',
            'alpha2' => 'SY',
            'alpha3' => 'SYR',
        ],
        'SZ' => [
            'name' => 'Swaziland',
            'alpha2' => 'SZ',
            'alpha3' => 'SWZ',
        ],
        'TC' => [
            'name' => 'Turks and Caicos Islands',
            'alpha2' => 'TC',
            'alpha3' => 'TCA',
        ],
        'TD' => [
            'name' => 'Chad',
            'alpha2' => 'TD',
            'alpha3' => 'TCD',
        ],
        'TF' => [
            'name' => 'French Southern Territories',
            'alpha2' => 'TF',
            'alpha3' => 'ATF',
        ],
        'TG' => [
            'name' => 'Togo',
            'alpha2' => 'TG',
            'alpha3' => 'TGO',
        ],
        'TH' => [
            'name' => 'Thailand',
            'alpha2' => 'TH',
            'alpha3' => 'THA',
        ],
        'TJ' => [
            'name' => 'Tajikistan',
            'alpha2' => 'TJ',
            'alpha3' => 'TJK',
        ],
        'TK' => [
            'name' => 'Tokelau',
            'alpha2' => 'TK',
            'alpha3' => 'TKL',
        ],
        'TL' => [
            'name' => 'Timor-Leste',
            'alpha2' => 'TL',
            'alpha3' => 'TLS',
        ],
        'TM' => [
            'name' => 'Turkmenistan',
            'alpha2' => 'TM',
            'alpha3' => 'TKM',
        ],
        'TN' => [
            'name' => 'Tunisia',
            'alpha2' => 'TN',
            'alpha3' => 'TUN',
        ],
        'TO' => [
            'name' => 'Tonga',
            'alpha2' => 'TO',
            'alpha3' => 'TON',
        ],
        'TR' => [
            'name' => 'Turkey',
            'alpha2' => 'TR',
            'alpha3' => 'TUR',
        ],
        'TT' => [
            'name' => 'Trinidad and Tobago',
            'alpha2' => 'TT',
            'alpha3' => 'TTO',
        ],
        'TV' => [
            'name' => 'Tuvalu',
            'alpha2' => 'TV',
            'alpha3' => 'TUV',
        ],
        'TW' => [
            'name' => 'Taiwan,
            Province of China',
            'alpha2' => 'TW',
            'alpha3' => 'TWN',
        ],
        'TZ' => [
            'name' => 'Tanzania,
            United Republic of',
            'alpha2' => 'TZ',
            'alpha3' => 'TZA',
        ],
        'UA' => [
            'name' => 'Ukraine',
            'alpha2' => 'UA',
            'alpha3' => 'UKR',
        ],
        'UG' => [
            'name' => 'Uganda',
            'alpha2' => 'UG',
            'alpha3' => 'UGA',
        ],
        'UM' => [
            'name' => 'United States Minor Outlying Islands',
            'alpha2' => 'UM',
            'alpha3' => 'UMI',
        ],
        'US' => [
            'name' => 'United States of America',
            'alpha2' => 'US',
            'alpha3' => 'USA',
        ],
        'UY' => [
            'name' => 'Uruguay',
            'alpha2' => 'UY',
            'alpha3' => 'URY',
        ],
        'UZ' => [
            'name' => 'Uzbekistan',
            'alpha2' => 'UZ',
            'alpha3' => 'UZB',
        ],
        'VA' => [
            'name' => 'Holy See (Vatican City State)',
            'alpha2' => 'VA',
            'alpha3' => 'VAT',
        ],
        'VC' => [
            'name' => 'Saint Vincent and the Grenadines',
            'alpha2' => 'VC',
            'alpha3' => 'VCT',
        ],
        'VE' => [
            'name' => 'Venezuela (Bolivarian Republic of)',
            'alpha2' => 'VE',
            'alpha3' => 'VEN',
        ],
        'VG' => [
            'name' => 'Virgin Islands (British)',
            'alpha2' => 'VG',
            'alpha3' => 'VGB',
        ],
        'VI' => [
            'name' => 'Virgin Islands (U.S.)',
            'alpha2' => 'VI',
            'alpha3' => 'VIR',
        ],
        'VN' => [
            'name' => 'Viet Nam',
            'alpha2' => 'VN',
            'alpha3' => 'VNM',
        ],
        'VU' => [
            'name' => 'Vanuatu',
            'alpha2' => 'VU',
            'alpha3' => 'VUT',
        ],
        'WF' => [
            'name' => 'Wallis and Futuna',
            'alpha2' => 'WF',
            'alpha3' => 'WLF',
        ],
        'WS' => [
            'name' => 'Samoa',
            'alpha2' => 'WS',
            'alpha3' => 'WSM',
        ],
        'YE' => [
            'name' => 'Yemen',
            'alpha2' => 'YE',
            'alpha3' => 'YEM',
        ],
        'YT' => [
            'name' => 'Mayotte',
            'alpha2' => 'YT',
            'alpha3' => 'MYT',
        ],
        'ZA' => [
            'name' => 'South Africa',
            'alpha2' => 'ZA',
            'alpha3' => 'ZAF',
        ],
        'ZM' => [
            'name' => 'Zambia',
            'alpha2' => 'ZM',
            'alpha3' => 'ZMB',
        ],
        'ZW' => [
            'name' => 'Zimbabwe',
            'alpha2' => 'ZW',
            'alpha3' => 'ZWE',
        ],
    ];

    public function getPropertyByAlpha2(string $alpha2, string $property = 'name'): string
    {
        if (strlen($alpha2) !== 2) {
            throw new LogicException('Alpha2 code must have 2 characters', 1683376219);
        }
        $alpha2 = strtoupper($alpha2);
        if (array_key_exists($alpha2, $this->getCountryConfiguration())) {
            if (array_key_exists($property, $this->getCountryConfiguration()[$alpha2])) {
                return $this->getCountryConfiguration()[$alpha2][$property];
            }
        }
        return '';
    }

    /**
     * 1. Search in alpha2
     * 2. Search in alpha3
     * 3. Search in name
     *
     * @param string $searchTerm
     * @return string
     */
    public function getAlpha2ByAnyProperty(string $searchTerm): string
    {
        $result = $this->getAlpha2ByAlpha2($searchTerm);
        if ($result === '') {
            $result = $this->getAlpha2ByAlpha3($searchTerm);
        }
        if ($result === '') {
            $result = $this->getAlpha2ByName($searchTerm);
        }
        return $result;
    }

    /**
     *  [
     *      'at' => 'foo',
     *      'de' => 'bar',
     *  ]
     *
     * @param array $countries
     * @return array
     */
    public function extendAlpha2ArrayWithCountryNames(array $countries): array
    {
        $names = [];
        foreach (array_keys($countries) as $alpha2) {
            if (strlen($alpha2) !== 2) {
                continue;
            }
            $alpha2 = strtoupper($alpha2);
            $names[$alpha2] = $this->getPropertyByAlpha2($alpha2);
        }
        return $names;
    }

    public function getCountryConfiguration(): array
    {
        return $this->countries;
    }

    protected function getAlpha2ByAlpha2(string $searchTerm): string
    {
        $result = array_filter($this->getCountryConfiguration(), function ($value) use ($searchTerm) {
            return strtoupper($searchTerm) === $value['alpha2'];
        });

        if (count($result) > 0) {
            return array_keys($result)[0];
        }
        return '';
    }

    protected function getAlpha2ByAlpha3(string $searchTerm): string
    {
        $result = array_filter($this->getCountryConfiguration(), function ($value) use ($searchTerm) {
            return strtoupper($searchTerm) === $value['alpha3'];
        });

        if (count($result) > 0) {
            return array_keys($result)[0];
        }
        return '';
    }

    protected function getAlpha2ByName(string $searchTerm): string
    {
        $result = array_filter($this->getCountryConfiguration(), function ($value) use ($searchTerm) {
            return stripos($value['name'], $searchTerm) !== false;
        });

        if (count($result) > 0) {
            return array_keys($result)[0];
        }
        return '';
    }
}
