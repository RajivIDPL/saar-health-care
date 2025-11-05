// Real AJAX implementation
fetch('approve_appointment.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        appointment_id: appointmentId,
        meeting_link: meetingLink
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        showAppointmentMessage(data.message, 'success');
        // Remove row and update stats
    } else {
        showAppointmentMessage(data.message, 'error');
        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;
    }
})
.catch(error => {
    showAppointmentMessage('Network error. Please try again.', 'error');
    button.innerHTML = originalText;
    button.disabled = false;
});