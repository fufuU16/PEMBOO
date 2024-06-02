document.addEventListener("DOMContentLoaded", function() {
    var purposeSelect = document.getElementById('purpose');
    var otherPurposeGroup = document.getElementById('other-purpose-group');
    var otherPurposeInput = document.getElementById('other-purpose');

    purposeSelect.addEventListener('change', function() {
        if (purposeSelect.value === 'Other') {
            otherPurposeGroup.style.display = 'block';
            otherPurposeInput.required = true;
        } else {
            otherPurposeGroup.style.display = 'none';
            otherPurposeInput.required = false;
        }
    });
});
document.addEventListener("DOMContentLoaded", function() {
    var scheduleInput = document.getElementById('schedule');

    scheduleInput.addEventListener('input', function() {
        var selectedDate = new Date(scheduleInput.value);
        var dayOfWeek = selectedDate.getDay(); // 0 for Sunday, 1 for Monday, ..., 6 for Saturday
        var hours = selectedDate.getHours();

        // Check if selected day is not Saturday or Sunday and time is between 8am and 5pm
        if ((dayOfWeek !== 0 && dayOfWeek !== 6) && (hours >= 8 && hours < 17)) {
            scheduleInput.setCustomValidity(''); // Clear any previous error message
        } else {
            scheduleInput.setCustomValidity('Please select a weekday between 8am and 5pm.'); // Set error message
        }
    });
});
