# Usage

[![GitHub tag](https://img.shields.io/github/tag/applicazza/appointed.svg)]()&nbsp;[![license](https://img.shields.io/github/license/applicazza/appointed.svg)]()&nbsp;[![Packagist](https://img.shields.io/packagist/dm/applicazza/appointed.svg)]()

* Do not forget to set up default timezone, i.e.

```php
date_default_timezone_set('Asia/Jerusalem');
```

* Instantiate a business day object and fill it with business hours

```php
$business_day = new BusinessDay;
$business_day->addBusinessHours(
    new Period(today( 8, 00), today(14, 00)),
    new Period(today(16, 00), today(19, 00))
);
```

* Add existing appointments to business day (optional)

```php
$business_day->addOccupiedSlots(
    new Period(today( 9, 15), today( 9, 30)),
    new Period(today(10, 30), today(11, 00)),
    new Period(today(16, 00), today(17, 00)))
)
```

* To test whether new appointment will fit

```php
$desired_period = new Period(today(10, 30), today(11, 30));

$business_day->isAvailableAt($period);

// or

$business_day->isNotAvailableAt($period);
```

* To get nearest available slot for an appointment

```php
$available_after = $business_day->closestFor($desired_period);

// or if you want to get available slot BEFORE

$available_before = $business_day->closestFor($desired_period, true);
```

* To get free slots (an order array of Period objects)

```php
$free_slots = $business_day->getFreeSlots();
```

