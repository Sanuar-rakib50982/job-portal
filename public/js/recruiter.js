document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("click", function (event) {
        if (!event.target.classList.contains("update-status-btn")) {
            return;
        }

        const button = event.target;
        const applicationId = button.getAttribute("data-application-id");
        const statusSelect = document.getElementById("statusSelect-" + applicationId);
        const statusBadge = document.getElementById("statusBadge-" + applicationId);
        const statusMessage = document.getElementById("statusMessage");

        if (!applicationId || !statusSelect) {
            alert("Invalid application data.");
            return;
        }

        const formData = new FormData();
        formData.append("application_id", applicationId);
        formData.append("status", statusSelect.value);

        fetch("../../../api/recruiter/update_application_status.php", {
            method: "POST",
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (statusBadge) {
                        statusBadge.textContent = data.status;
                        statusBadge.className = "badge " + getBadgeClass(data.status);
                    }

                    if (statusMessage) {
                        statusMessage.innerHTML = '<div class="alert-success">' + escapeHtml(data.message) + '</div>';
                    }
                } else {
                    if (statusMessage) {
                        statusMessage.innerHTML = '<div class="alert-error">' + escapeHtml(data.message) + '</div>';
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(function () {
                if (statusMessage) {
                    statusMessage.innerHTML = '<div class="alert-error">Something went wrong while updating status.</div>';
                } else {
                    alert("Something went wrong while updating status.");
                }
            });
    });

    function getBadgeClass(status) {
        if (status === "submitted") {
            return "yellow";
        }

        if (status === "shortlisted" || status === "interview") {
            return "green";
        }

        if (status === "rejected") {
            return "red";
        }

        return "gray";
    }

    function escapeHtml(text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
});