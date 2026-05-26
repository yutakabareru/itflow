<?php

// If client_id is in URI then show client Side Bar and client header
if (isset($_GET['client_id'])) {
    require_once "includes/inc_all_client.php";
    $client_event_query = "WHERE event_client_id = $client_id";
    $client_query = "WHERE 1 = 1 AND client_id = $client_id";
    $client_url = "&client_id=$client_id";
} else {
    require_once "includes/inc_all.php";
    $client_event_query = '';
    $client_query = 'WHERE 1 = 1';
    $client_url = '';
}

if (isset($_GET['calendar_id'])) {
    $calendar_selected_id = intval($_GET['calendar_id']);
}

?>

<!-- So that when hovering over a created event it turns into a hand instead of cursor -->
<style>
    .fc-event {
        cursor: pointer;
    }
</style>

<div class="row">

    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Calendars</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool ajax-modal" data-modal-url="modals/calendar/calendar_add.php"><i class="fas fa-plus" title="New Calendar"></i></button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $sql = mysqli_query($mysqli, "SELECT * FROM calendars");
                while ($row = mysqli_fetch_assoc($sql)) {
                    $calendar_id = intval($row['calendar_id']);
                    $calendar_name = nullable_htmlentities($row['calendar_name']);
                    $calendar_color = nullable_htmlentities($row['calendar_color']);
                ?>
                <div class="form-group d-flex align-items-center">
                    <i class="fas fa-fw fa-circle mr-2" style="color:<?= $calendar_color ?>;"></i><?= $calendar_name ?>

                    <div class="dropdown dropright ml-auto">
                        <button class="btn btn-tool" type="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item ajax-modal" href="#"
                                data-modal-url="modals/calendar/calendar_edit.php?id=<?= $calendar_id ?>">
                                <i class="fas fa-fw fa-pencil-alt mr-2"></i>Rename
                            </a>
                            <?php if ($session_user_role == 3) { ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger text-bold confirm-link" href="post.php?delete_calendar=<?= $calendar_id ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>">
                                    <i class="fas fa-fw fa-trash mr-2"></i>Delete
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>

            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div id='calendar'></div>
        </div>
    </div>

</div>

<?php

require_once "modals/calendar/calendar_event_add.php";

//loop through IDs and create a modal for each
$sql = mysqli_query($mysqli, "SELECT * FROM calendar_events LEFT JOIN calendars ON event_calendar_id = calendar_id $client_event_query");
while ($row = mysqli_fetch_assoc($sql)) {
    $event_id = intval($row['event_id']);
    $event_title = nullable_htmlentities($row['event_title']);
    $event_description = nullable_htmlentities($row['event_description']);
    $event_location = nullable_htmlentities($row['event_location']);
    $event_start = nullable_htmlentities($row['event_start']);
    $event_end = nullable_htmlentities($row['event_end']);
    $event_repeat = nullable_htmlentities($row['event_repeat']);
    $calendar_id = intval($row['calendar_id']);
    $calendar_name = nullable_htmlentities($row['calendar_name']);
    $calendar_color = nullable_htmlentities($row['calendar_color']);
    $client_id = intval($row['event_client_id']);
}

?>

<?php require_once "../includes/footer.php";
?>

<script src='/plugins/fullcalendar/dist/index.global.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap',
            defaultView: 'dayGridMonth',
            customButtons: {
                newEvent: {
                    text: 'New Event',
                    bootstrapFontAwesome: 'fas fa-plus',
                    click: function() {
                        $("#addCalendarEventModal").modal();
                    }
                }
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth newEvent'
            },
            <?php if (!$session_mobile) {
            ?>aspectRatio: 2.5,
        <?php } else { ?>
            aspectRatio: 0.7,
        <?php } ?>
        navLinks: true, // can click day/week names to navigate views
        selectable: true,
        height: '90vh',

        selectMirror: true,
        eventDidMount: function(info) {
            // Always show full title when hovering
            info.el.setAttribute('title', info.event.title);
        },
        eventClick: function(editEvent) {
            var eventId = editEvent.event.id;
            var $link = $('<a>', {
                href: '#',
                'class': 'ajax-modal',
                'data-modal-url': 'modals/calendar/calendar_event_edit.php?<?php echo $client_url; ?>&id=' + eventId
            });

            $('body').append($link); // Append to the body
            $link.trigger('click');  // Trigger the modal
            $link.remove(); // Cleanup
        },
        dayMaxEvents: true, // allow "more" link when too many events
        views: {
            timeGrid: {
                dayMaxEventRows: 3, // adjust to 6 only for timeGridWeek/timeGridDay
                expandRows: true,
                nowIndicator: true,
                eventMaxStack: 1,
            },
            dayGrid: {
                dayMaxEvents: 3, // adjust to 6 only for timeGridWeek/timeGridDay
                expandRows: true,
            },

        },
        events: [
            <?php
            $sql = mysqli_query($mysqli, "SELECT * FROM calendar_events LEFT JOIN calendars ON event_calendar_id = calendar_id $client_event_query");
            while ($row = mysqli_fetch_assoc($sql)) {
                $event_id = intval($row['event_id']);
                $event_title = json_encode($row['event_title']);
                $event_start = json_encode($row['event_start']);
                $event_end = json_encode($row['event_end']);
                $calendar_id = intval($row['calendar_id']);
                $calendar_name = json_encode($row['calendar_name']);
                $calendar_color = json_encode($row['calendar_color']);

                echo "{ id: $event_id, title: $event_title, start: $event_start, end: $event_end, color: $calendar_color },";
            }
            ?>
        ],
        eventOrder: 'allDay,start,-duration,title',

        <?php
        // User preference for Calendar start day (Sunday/Monday)
        // Fetch User Dashboard Settings
        $row = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT user_config_calendar_first_day FROM user_settings WHERE user_id = $session_user_id"));
        $user_config_calendar_first_day = intval($row['user_config_calendar_first_day']);
        ?>
        firstDay: <?php echo $user_config_calendar_first_day ?>,
        });

        calendar.render();
    });
</script>

<!-- Automatically set new event end date to 1 hr after start date -->
<script>
    // Function - called when user leaves field (onblur)
    function updateIncrementEndTime() {

        // Get the start date
        let start = document.getElementById("event_add_start").value;

        // Create a date object
        let new_end = new Date(start);

        // Get the time zone offset in minutes, convert it to milliseconds
        let offsetInMilliseconds = new_end.getTimezoneOffset() * 60 * 1000;

        // Adjust the date by the time zone offset before adding an hour
        new_end = new Date(new_end.getTime() - offsetInMilliseconds);

        // Set the end date to 1 hr in the future
        new_end.setHours(new_end.getHours() + 1);

        // Get the date back as a string, with the milliseconds trimmed off
        new_end = new_end.toISOString().replace(/.\d+Z$/g, "");

        // Update the end date field
        document.getElementById("event_add_end").value = new_end;
    }
</script>
