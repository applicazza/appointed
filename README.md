[![GitHub tag](https://img.shields.io/github/tag/applicazza/appointed.svg)]()&nbsp;[![license](https://img.shields.io/github/license/applicazza/appointed.svg)]()&nbsp;[![Packagist](https://img.shields.io/packagist/dm/applicazza/appointed.svg)]()

# Installation

```
composer require applicazza/appointed
```

# Usage

* Set up default timezone, i.e.

```php
date_default_timezone_set('Asia/Jerusalem');
```

* Instantiate business day

```php
use Applicazza\Appointed\BusinessDay;

// ...
$business_day = new BusinessDay;
```

* Create some operating periods and add them to business day. If some operating periods cannot be added then false will be returned and no changes will be applied whatsoever

```php
use Applicazza\Appointed\Period;

// ...

$period_0900_1400 = Period::make(today( 9, 00), today(14, 00));
$period_1600_1900 = Period::make(today(16, 00), today(19, 00));

$business_day->addOperatingPeriods(
    $period_1600_1900,
    $period_0900_1400
);
```

* Create some appointments and add them to business day. If some appointments cannot be added then false will be returned and no changes will be applied whatsoever

```php
use Applicazza\Appointed\Appointment;

// ...

$appointment_1100_1130 = Appointment::make(today( 11, 00), today(11, 30));
$appointment_1200_1330 = Appointment::make(today( 12, 00), today(13, 30));

$business_day->addAppointments(
    $appointment_1100_1130,
    $appointment_1200_1330
);
```

* To fit previously failed appointment use ```BusinessDay::fit(Appointment $appointment, $direction = 'forward')``` that will return either null or recommended Appointment
```php
$business_day->fit($appointment_1300_1330, 'backward');
$business_day->fit($appointment_1300_1330);
```

* To delete appointment(s) use ```BusinessDay::deleteAppointments(Appointment ...$appointment)``` that will return boolean
```php
$business_day->deleteAppointments($appointment_1300_1330);
```

* To delete operating period use ```BusinessDay::deleteOperatingPeriod(Period $period)``` that will return boolean
```php
$business_day->deleteOperatingPeriod($period_0900_1400)
```

* To edit operating period use ```BusinessDay::editOperatingPeriod(Period $old_period, Period $new_period)``` that will return boolean
```php
$business_day->editOperatingPeriod($period_1330_1400, $period_1330_1430)
```

* To get whole day agenda use ```BusinessDay::getAgenda()``` that will return array of Period and/or Appointment ordered ascending. Since underlying classes implement JsonSerializable interface this data may be easily encoded to json.
```php
$agenda = $business_day->getAgenda();
// To pretty print results
echo json_encode($business_day->getAgenda(), JSON_PRETTY_PRINT), PHP_EOL;
```

## Helpers

```php
use function Applicazza\Appointed\interval;
use function Applicazza\Appointed\today;
// ...
interval($hours = 0, $minutes = 0, $seconds = 0);
today($hours = 0, $minutes = 0, $seconds = 0);
```


