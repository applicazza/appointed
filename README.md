# Usage

* Do not forget to set up default timezone, i.e.

```php
date_default_timezone_set('Asia/Jerusalem');
```

* Instantiate a business day object

```php
$business_day_starts_at = Carbon::today()->addHours(9);
$business_day_ends_at   = Carbon::today()->addHours(18);

$business_day = new BusinessDay($business_day_starts_at, $business_day_ends_at);
```

* Add existing appointments to business day (optional)

```php
$business_day->addAppointments(
    new Appointment(Carbon::today()->addHours(10), Carbon::today()->addHours(11)), // 10:00 - 11:00
    new Appointment(Carbon::today()->addHours(9), Carbon::today()->addHours(9)->addMinutes(30)), // 9:00 - 9:30
    new Appointment(Carbon::today()->addHours(11)->addMinutes(45), Carbon::today()->addHours(12)->addMinutes(30)) // 11:45 - 12:30
);
```

* To test whether new appointment will fit, surround with try/catch, i.e.

```php
$new_appointment = new Appointment(Carbon::today()->addHours(9, 45), Carbon::today()->addHours(10)->addMinutes(15)); // 9:45 - 10:15
try {
    $business_day->addAppointments($new_appointment);
} catch (OverlappingAppointmentException $e) {
    $offered_appointment_before = $business_day->offerBefore($new_appointment);
    $offered_appointment_after = $business_day->offerAfter($new_appointment);
}
```