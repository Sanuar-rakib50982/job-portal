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

document.addEventListener("DOMContentLoaded", function () {
    const seekerSearchForm = document.getElementById("seekerSearchForm");
    const seekerResults = document.getElementById("seekerResults");
    const resetSeekerSearch = document.getElementById("resetSeekerSearch");

    if (!seekerSearchForm || !seekerResults) {
        return;
    }

    function loadSeekers() {
        const formData = new FormData(seekerSearchForm);
        const params = new URLSearchParams(formData);

        fetch("../../../api/recruiter/search_seekers.php?" + params.toString())
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    seekerResults.innerHTML = "<p>" + escapeRecruiterHtml(data.message) + "</p>";
                    return;
                }

                if (data.seekers.length === 0) {
                    seekerResults.innerHTML = "<p>No seekers found.</p>";
                    return;
                }

                let output = "";

                data.seekers.forEach(function (seeker) {
                    let resumeButton = "";

                    if (seeker.resume_path) {
                        resumeButton = `<a class="btn btn-secondary" href="../../../${escapeRecruiterHtml(seeker.resume_path)}" target="_blank">View Resume</a>`;
                    }

                    output += `
                        <div class="job-card">
                            <h3>${escapeRecruiterHtml(seeker.name)}</h3>

                            <p>
                                <strong>Email:</strong> ${escapeRecruiterHtml(seeker.email || "N/A")}<br>
                                <strong>Phone:</strong> ${escapeRecruiterHtml(seeker.phone || "N/A")}<br>
                                <strong>Headline:</strong> ${escapeRecruiterHtml(seeker.headline || "No headline")}<br>
                                <strong>Skills:</strong> ${escapeRecruiterHtml(seeker.skills || "N/A")}<br>
                                <strong>Experience:</strong> ${escapeRecruiterHtml(seeker.years_experience || "0")} years<br>
                                <strong>Education:</strong> ${escapeRecruiterHtml(seeker.education_level || "N/A")}<br>
                                <strong>Preferred Location:</strong> ${escapeRecruiterHtml(seeker.preferred_location || "N/A")}
                            </p>

                            <a class="btn" href="seeker_profile.php?id=${seeker.id}">View Profile</a>
                            ${resumeButton}
                        </div>
                    `;
                });

                seekerResults.innerHTML = output;
            })
            .catch(function () {
                seekerResults.innerHTML = "<p>Something went wrong while searching seekers.</p>";
            });
    }

    seekerSearchForm.addEventListener("submit", function (event) {
        event.preventDefault();
        loadSeekers();
    });

    const inputs = seekerSearchForm.querySelectorAll("input");

    inputs.forEach(function (input) {
        input.addEventListener("keyup", loadSeekers);
        input.addEventListener("change", loadSeekers);
    });

    if (resetSeekerSearch) {
        resetSeekerSearch.addEventListener("click", function () {
            seekerSearchForm.reset();
            loadSeekers();
        });
    }

    function escapeRecruiterHtml(text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
});