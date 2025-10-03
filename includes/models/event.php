<?php

/**
 * Speaker class to represent a speaker/contributor
 */
class Speaker
{
  public $firstName;
  public $lastName;
  public $fullName;
  public $affiliation;
  public $emailHash;
  public $id;

  public function __construct($data)
  {
    $this->firstName = $data['first_name'] ?? '';
    $this->lastName = $data['last_name'] ?? '';
    $this->fullName = $data['fullName'] ?? '';
    $this->affiliation = $data['affiliation'] ?? '';
    $this->emailHash = $data['emailHash'] ?? '';
    $this->id = $data['id'] ?? null;
  }

  public function toArray()
  {
    return [
      'firstName' => $this->firstName,
      'lastName' => $this->lastName,
      'fullName' => $this->fullName,
      'affiliation' => $this->affiliation,
      'emailHash' => $this->emailHash,
      'id' => $this->id
    ];
  }
}

/**
 * Contribution class to represent individual talks/presentations
 */
class Contribution
{
  public $id;
  public $dbId;
  public $friendlyId;
  public $title;
  public $description;
  public $startDate;
  public $startTime;
  public $endDate;
  public $endTime;
  public $duration;
  public $location;
  public $room;
  public $roomFullname;
  public $url;
  public $type;
  public $boardNumber;
  public $code;
  public $sessionTitle;
  public $timezone;

  /** @var Speaker[] */
  public $speakers = [];

  public function __construct($data, $timezone = 'UTC')
  {
    $this->id = $data['id'] ?? null;
    $this->dbId = $data['db_id'] ?? null;
    $this->friendlyId = $data['friendly_id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->description = $data['description'] ?? '';
    $this->duration = $data['duration'] ?? 0;
    $this->location = $data['location'] ?? '';
    $this->room = $data['room'] ?? '';
    $this->roomFullname = $data['roomFullname'] ?? '';
    $this->url = $data['url'] ?? '';
    $this->type = $data['type'] ?? null;
    $this->boardNumber = $data['board_number'] ?? '';
    $this->code = $data['code'] ?? '';
    $this->sessionTitle = $data['session'] ?? '';
    $this->timezone = $timezone;

    // Parse dates
    if (isset($data['startDate'])) {
      $this->startDate = $data['startDate']['date'] ?? null;
      $this->startTime = $data['startDate']['time'] ?? null;
    }

    if (isset($data['endDate'])) {
      $this->endDate = $data['endDate']['date'] ?? null;
      $this->endTime = $data['endDate']['time'] ?? null;
    }

    // Parse speakers
    if (isset($data['speakers']) && is_array($data['speakers'])) {
      foreach ($data['speakers'] as $speakerData) {
        $this->speakers[] = new Speaker($speakerData);
      }
    }
  }

  /**
   * Get start datetime as DateTime object
   */
  public function getStartDateTime()
  {
    if ($this->startDate && $this->startTime) {
      return new DateTime($this->startDate . ' ' . $this->startTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get end datetime as DateTime object
   */
  public function getEndDateTime()
  {
    if ($this->endDate && $this->endTime) {
      return new DateTime($this->endDate . ' ' . $this->endTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get formatted start datetime string
   */
  public function getFormattedStartDate($format = 'Y-m-d H:i:s')
  {
    $dt = $this->getStartDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get formatted end datetime string
   */
  public function getFormattedEndDate($format = 'Y-m-d H:i:s')
  {
    $dt = $this->getEndDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get duration in hours
   */
  public function getDurationInHours()
  {
    return $this->duration / 60;
  }

  /**
   * Get speaker names as comma-separated string
   */
  public function getSpeakerNames()
  {
    return implode(', ', array_map(function ($speaker) {
      return $speaker->fullName;
    }, $this->speakers));
  }

  /**
   * Get data formatted for calendar export
   */
  public function getCalendarExportData()
  {
    return [
      'title' => $this->title,
      'start' => $this->getFormattedStartDate(),
      'end' => $this->getFormattedEndDate(),
      'location' => $this->location,
      'description' => $this->description . "\n\nSpeakers: " . $this->getSpeakerNames(),
      'url' => $this->url
    ];
  }

  /**
   * Convert to array
   */
  public function toArray()
  {
    return [
      'id' => $this->id,
      'dbId' => $this->dbId,
      'friendlyId' => $this->friendlyId,
      'title' => $this->title,
      'description' => $this->description,
      'startDate' => $this->startDate,
      'startTime' => $this->startTime,
      'endDate' => $this->endDate,
      'endTime' => $this->endTime,
      'duration' => $this->duration,
      'location' => $this->location,
      'room' => $this->room,
      'url' => $this->url,
      'sessionTitle' => $this->sessionTitle,
      'speakers' => array_map(function ($speaker) {
        return $speaker->toArray();
      }, $this->speakers)
    ];
  }
}

/**
 * Session class to represent event sessions
 */
class Session
{
  public $id;
  public $dbId;
  public $friendlyId;
  public $title;
  public $description;
  public $startDate;
  public $startTime;
  public $endDate;
  public $endTime;
  public $location;
  public $room;
  public $roomFullname;
  public $address;
  public $url;
  public $color;
  public $textColor;
  public $type;
  public $code;
  public $slotTitle;
  public $isPoster;
  public $numSlots;
  public $inheritLoc;
  public $inheritRoom;
  public $timezone;

  /** @var Contribution[] */
  public $contributions = [];

  /** @var Speaker[] */
  public $conveners = [];

  public function __construct($data, $timezone = 'UTC')
  {
    $this->id = $data['id'] ?? null;
    $this->title = $data['title'] ?? '';
    $this->description = $data['description'] ?? '';
    $this->location = $data['location'] ?? '';
    $this->room = $data['room'] ?? '';
    $this->roomFullname = $data['roomFullname'] ?? '';
    $this->address = $data['address'] ?? '';
    $this->url = $data['url'] ?? '';
    $this->slotTitle = $data['slotTitle'] ?? '';
    $this->code = $data['code'] ?? '';
    $this->inheritLoc = $data['inheritLoc'] ?? false;
    $this->inheritRoom = $data['inheritRoom'] ?? false;
    $this->timezone = $timezone;

    // Parse dates
    if (isset($data['startDate'])) {
      $this->startDate = $data['startDate']['date'] ?? null;
      $this->startTime = $data['startDate']['time'] ?? null;
    }

    if (isset($data['endDate'])) {
      $this->endDate = $data['endDate']['date'] ?? null;
      $this->endTime = $data['endDate']['time'] ?? null;
    }

    // Parse session metadata if available
    if (isset($data['session'])) {
      $session = $data['session'];
      $this->dbId = $session['db_id'] ?? null;
      $this->friendlyId = $session['friendly_id'] ?? null;
      $this->color = $session['color'] ?? '';
      $this->textColor = $session['textColor'] ?? '';
      $this->type = $session['type'] ?? null;
      $this->isPoster = $session['isPoster'] ?? false;
      $this->numSlots = $session['numSlots'] ?? 1;
    }

    // Parse contributions
    if (isset($data['contributions']) && is_array($data['contributions'])) {
      foreach ($data['contributions'] as $contribData) {
        $this->contributions[] = new Contribution($contribData, $timezone);
      }
    }

    // Parse conveners
    if (isset($data['conveners']) && is_array($data['conveners'])) {
      foreach ($data['conveners'] as $convenerData) {
        $this->conveners[] = new Speaker($convenerData);
      }
    }
  }

  /**
   * Get start datetime as DateTime object
   */
  public function getStartDateTime()
  {
    if ($this->startDate && $this->startTime) {
      return new DateTime($this->startDate . ' ' . $this->startTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get end datetime as DateTime object
   */
  public function getEndDateTime()
  {
    if ($this->endDate && $this->endTime) {
      return new DateTime($this->endDate . ' ' . $this->endTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get formatted start datetime string
   */
  public function getFormattedStartDate($format = 'Y-m-d H:i:s')
  {
    $dt = $this->getStartDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get formatted end datetime string
   */
  public function getFormattedEndDate($format = 'Y-m-d H:i:s')
  {
    $dt = $this->getEndDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get duration in hours
   */
  public function getDurationInHours()
  {
    $start = $this->getStartDateTime();
    $end = $this->getEndDateTime();
    if ($start && $end) {
      return ($end->getTimestamp() - $start->getTimestamp()) / 3600;
    }
    return 0;
  }

  /**
   * Get all contributions
   */
  public function getContributions()
  {
    return $this->contributions;
  }

  /**
   * Get contributions sorted by start time
   */
  public function getContributionsSorted()
  {
    $contribs = $this->contributions;
    usort($contribs, function ($a, $b) {
      $timeA = $a->getStartDateTime();
      $timeB = $b->getStartDateTime();
      if (!$timeA || !$timeB) return 0;
      return $timeA->getTimestamp() - $timeB->getTimestamp();
    });
    return $contribs;
  }

  /**
   * Get data formatted for calendar export
   */
  public function getCalendarExportData()
  {
    return [
      'title' => $this->title,
      'start' => $this->getFormattedStartDate(),
      'end' => $this->getFormattedEndDate(),
      'location' => $this->location,
      'description' => $this->description,
      'url' => $this->url
    ];
  }

  /**
   * Convert to array
   */
  public function toArray()
  {
    return [
      'id' => $this->id,
      'dbId' => $this->dbId,
      'friendlyId' => $this->friendlyId,
      'title' => $this->title,
      'description' => $this->description,
      'startDate' => $this->startDate,
      'startTime' => $this->startTime,
      'endDate' => $this->endDate,
      'endTime' => $this->endTime,
      'location' => $this->location,
      'room' => $this->room,
      'url' => $this->url,
      'color' => $this->color,
      'contributions' => array_map(function ($contrib) {
        return $contrib->toArray();
      }, $this->contributions),
      'conveners' => array_map(function ($convener) {
        return $convener->toArray();
      }, $this->conveners)
    ];
  }
}

/**
 * Event class to parse Indico JSON metadata
 */
class Event
{
  private $data;

  // Basic event properties
  public $id;
  public $title;
  public $raw_description;
  public $description;
  public $type;
  public $url;
  public $location;
  public $address;
  public $room;
  public $timezone;
  public $category;
  public $categoryId;

  // Date/time properties
  public $startDate;
  public $startTime;
  public $endDate;
  public $endTime;
  public $creationDate;

  // Related data
  public $creator;

  /** @var Session[] */
  public $sessions = [];

  /**
   * Constructor - parses JSON data
   * 
   * @param string|array $jsonData JSON string or decoded array
   */
  public function __construct($jsonData)
  {
    // Decode JSON if string provided
    if (is_string($jsonData)) {
      $this->data = json_decode($jsonData, true);
    } else {
      $this->data = $jsonData;
    }

    // Check if valid Indico export
    if (!isset($this->data['results'][0])) {
      throw new Exception('Invalid Indico JSON format');
    }

    $event = $this->data['results'][0];

    // Parse basic properties
    $this->parseBasicInfo($event);
    $this->parseDates($event);
    $this->parseCreator($event);
    $this->parseSessions($event);
  }

  /**
   * Parse basic event information
   */
  private function parseBasicInfo($event)
  {
    $this->id = $event['id'] ?? null;
    $this->title = $event['title'] ?? '';
    $this->raw_description = $event['description'] ?? '';
    $this->description = $this->stripHtmlTags($event['description'] ?? '');
    $this->type = $event['type'] ?? '';
    $this->url = $event['url'] ?? '';
    $this->location = $event['location'] ?? '';
    $this->address = $event['address'] ?? '';
    $this->room = $event['room'] ?? '';
    $this->timezone = $event['timezone'] ?? 'UTC';
    $this->category = $event['category'] ?? '';
    $this->categoryId = $event['categoryId'] ?? null;
  }

  /**
   * Parse date and time information
   */
  private function parseDates($event)
  {
    if (isset($event['startDate'])) {
      $this->startDate = $event['startDate']['date'] ?? null;
      $this->startTime = $event['startDate']['time'] ?? null;
    }

    if (isset($event['endDate'])) {
      $this->endDate = $event['endDate']['date'] ?? null;
      $this->endTime = $event['endDate']['time'] ?? null;
    }

    if (isset($event['creationDate'])) {
      $this->creationDate = $event['creationDate']['date'] ?? null;
    }
  }

  /**
   * Parse creator information
   */
  private function parseCreator($event)
  {
    if (isset($event['creator'])) {
      $this->creator = new Speaker($event['creator']);
    }
  }

  /**
   * Parse sessions
   */
  private function parseSessions($event)
  {
    if (!isset($event['sessions']) || !is_array($event['sessions'])) {
      return;
    }

    foreach ($event['sessions'] as $sessionData) {
      $this->sessions[] = new Session($sessionData, $this->timezone);
    }
  }

  /**
   * Strip HTML tags from description
   */
  private function stripHtmlTags($html)
  {
    return strip_tags($html);
  }

  /**
   * Get start datetime as DateTime object
   */
  public function getStartDateTime()
  {
    if ($this->startDate && $this->startTime) {
      return new DateTime($this->startDate . ' ' . $this->startTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get end datetime as DateTime object
   */
  public function getEndDateTime()
  {
    if ($this->endDate && $this->endTime) {
      return new DateTime($this->endDate . ' ' . $this->endTime, new DateTimeZone($this->timezone));
    }
    return null;
  }

  /**
   * Get formatted start datetime string
   */
  public function getFormattedStartDate($format = 'Y-m-d H:i')
  {
    $dt = $this->getStartDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get formatted end datetime string
   */
  public function getFormattedEndDate($format = 'Y-m-d H:i')
  {
    $dt = $this->getEndDateTime();
    return $dt ? $dt->format($format) : '';
  }

  /**
   * Get all sessions
   */
  public function getSessions()
  {
    return $this->sessions;
  }

  /**
   * Get sessions sorted by start time
   */
  public function getSessionsSorted()
  {
    $sessions = $this->sessions;
    usort($sessions, function ($a, $b) {
      $timeA = $a->getStartDateTime();
      $timeB = $b->getStartDateTime();
      if (!$timeA || !$timeB) return 0;
      return $timeA->getTimestamp() - $timeB->getTimestamp();
    });
    return $sessions;
  }

  /**
   * Get all contributions from all sessions
   */
  public function getAllContributions()
  {
    $allContributions = [];
    foreach ($this->sessions as $session) {
      $allContributions = array_merge($allContributions, $session->getContributions());
    }
    return $allContributions;
  }

  /**
   * Get all contributions sorted by start time
   */
  public function getAllContributionsSorted()
  {
    $contribs = $this->getAllContributions();
    usort($contribs, function ($a, $b) {
      $timeA = $a->getStartDateTime();
      $timeB = $b->getStartDateTime();
      if (!$timeA || !$timeB) return 0;
      return $timeA->getTimestamp() - $timeB->getTimestamp();
    });
    return $contribs;
  }

  /**
   * Get data formatted for calendar export
   */
  public function getCalendarExportData()
  {
    return [
      'title' => $this->title,
      'start' => $this->getFormattedStartDate(),
      'end' => $this->getFormattedEndDate(),
      'location' => $this->location,
      'description' => $this->description,
      'url' => $this->url
    ];
  }

  /**
   * Convert to array for JSON serialization
   */
  public function toArray()
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'description' => $this->description,
      'type' => $this->type,
      'url' => $this->url,
      'location' => $this->location,
      'address' => $this->address,
      'room' => $this->room,
      'timezone' => $this->timezone,
      'category' => $this->category,
      'categoryId' => $this->categoryId,
      'startDate' => $this->startDate,
      'startTime' => $this->startTime,
      'endDate' => $this->endDate,
      'endTime' => $this->endTime,
      'creator' => $this->creator ? $this->creator->toArray() : null,
      'sessions' => array_map(function ($session) {
        return $session->toArray();
      }, $this->sessions)
    ];
  }
}

// Example usage:
/*
// Parse the JSON file
$jsonString = file_get_contents('event.json');
$event = new Event($jsonString);

// Access event properties
echo $event->title . "\n"; // "15 years of ISDD"
echo $event->location . "\n"; // "Domaine de Clairefontaine - Noyarey"
echo $event->creator->fullName . "\n"; // "Robert, Kimberley"

// Get all sessions
foreach ($event->getSessions() as $session) {
    echo "\nSession: " . $session->title . "\n";
    echo "Time: " . $session->getFormattedStartDate('H:i') . " - " . $session->getFormattedEndDate('H:i') . "\n";
    echo "Duration: " . $session->getDurationInHours() . " hours\n";
    
    // Get contributions within this session
    foreach ($session->getContributions() as $contribution) {
        echo "  - " . $contribution->title . "\n";
        echo "    Time: " . $contribution->getFormattedStartDate('H:i') . " - " . $contribution->getFormattedEndDate('H:i') . "\n";
        echo "    Speaker(s): " . $contribution->getSpeakerNames() . "\n";
    }
}

// Get all contributions across all sessions, sorted by time
echo "\n\nAll Contributions (chronological):\n";
foreach ($event->getAllContributionsSorted() as $contribution) {
    echo $contribution->getFormattedStartDate('H:i') . " - " . $contribution->title . "\n";
}

// Export a specific session to calendar
$session = $event->getSessions()[0];
$calData = $session->getCalendarExportData();

// Export a specific contribution to calendar
$contribution = $event->getAllContributions()[0];
$contribCalData = $contribution->getCalendarExportData();
*/
