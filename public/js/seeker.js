document.addEventListener("DOMContentLoaded", function () {
    const filterForm = document.getElementById("jobFilterForm");
    const jobResults = document.getElementById("jobResults");
    const resetButton = document.getElementById("resetFilters");

    if (!filterForm || !jobResults) {
        return;
    }

    function loadJobs() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        fetch("../../../api/seeker/filter_jobs.php?" + params.toString())
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    jobResults.innerHTML = "<p>" + data.message + "</p>";
                    return;
                }

                if (data.jobs.length === 0) {
                    jobResults.innerHTML = "<p>No active jobs found.</p>";
                    return;
                }

                let output = "";

                data.jobs.forEach(function (job) {
                    let featuredBadge = "";

                    if (job.is_featured == 1) {
                        featuredBadge = '<span class="badge yellow">Featured</span><br><br>';
                    }

                    output += `
                        <div class="job-card">
                            <h3>${escapeHtml(job.title)}</h3>

                            <p>
                                <strong>Category:</strong> ${escapeHtml(job.category_name || "N/A")}<br>
                                <strong>Employer:</strong> ${escapeHtml(job.employer_name || "N/A")}<br>
                                <strong>Recruiter:</strong> ${escapeHtml(job.recruiter_name || "N/A")}<br>
                                <strong>Location:</strong> ${escapeHtml(job.location || "N/A")}<br>
                                <strong>Type:</strong> ${escapeHtml(job.job_type || "N/A")}<br>
                                <strong>Experience:</strong> ${escapeHtml(job.experience_level || "N/A")}<br>
                                <strong>Salary:</strong> ${escapeHtml(job.salary_min || "0")} - ${escapeHtml(job.salary_max || "0")}<br>
                                <strong>Deadline:</strong> ${escapeHtml(job.deadline || "N/A")}
                            </p>

                            ${featuredBadge}

                            <a class="btn" href="job_details.php?id=${job.id}">View Details</a>
<button type="button" class="btn btn-secondary save-job-btn" data-job-id="${job.id}">
    ${job.is_saved ? "Unsave Job" : "Save Job"}
</button>
                        </div>
                    `;
                });

                jobResults.innerHTML = output;
            })
            .catch(function () {
                jobResults.innerHTML = "<p>Something went wrong while loading jobs.</p>";
            });
    }

    function escapeHtml(text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    filterForm.addEventListener("submit", function (event) {
        event.preventDefault();
        loadJobs();
    });

    const inputs = filterForm.querySelectorAll("input, select");

    inputs.forEach(function (input) {
        input.addEventListener("change", loadJobs);
    });

    const keywordInput = document.getElementById("keyword");

    if (keywordInput) {
        keywordInput.addEventListener("keyup", function () {
            loadJobs();
        });
    }

    if (resetButton) {
        resetButton.addEventListener("click", function () {
            filterForm.reset();
            loadJobs();
        });
    }
});

document.addEventListener("click", function (event) {
    if (!event.target.classList.contains("save-job-btn")) {
        return;
    }

    const button = event.target;
    const jobId = button.getAttribute("data-job-id");

    if (!jobId) {
        alert("Invalid job ID.");
        return;
    }

    const formData = new FormData();
    formData.append("job_id", jobId);

    fetch("../../../api/seeker/save_job.php", {
        method: "POST",
        body: formData
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            button.textContent = data.button_text;
            alert(data.message);
        } else {
            alert(data.message);
        }
    })
    .catch(function () {
        alert("Something went wrong while saving the job.");
    });
});