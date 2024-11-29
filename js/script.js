document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".card");

    cards.forEach((card) => {
        card.addEventListener("click", async () => {
            const sensorId = card.getAttribute("data-sensor-id");
            if (sensorId) {
                let currentPage = 1; 
                const logList = document.querySelector("#logList");
                const pagination = document.querySelector("#pagination");
                const logMessage = document.querySelector("#logMessage");

                const fetchLogs = async (page) => {
                    const response = await fetch(`api/get_logs.php?sensor_id=${sensorId}&page=${page}`);
                    const logs = await response.json();

                    if (logs.status === "success") {
                        logList.innerHTML = ""; 
                        pagination.innerHTML = ""; 

                        logs.data.forEach((log) => {
                            const logItem = document.createElement("li");
                            logItem.className = "list-group-item";
                            logItem.textContent = `kWh: ${log.data_kwh}, Updated: ${log.datetime}`;
                            logList.appendChild(logItem);
                        });

                        for (let i = 1; i <= logs.pages; i++) {
                            const pageItem = document.createElement("li");
                            pageItem.className = "page-item";
                            const pageLink = document.createElement("a");
                            pageLink.className = "page-link";
                            pageLink.textContent = i;
                            pageLink.href = "#";
                            pageLink.addEventListener("click", (e) => {
                                e.preventDefault(); 
                                fetchLogs(i); 
                            });

                            pageItem.appendChild(pageLink);
                            pagination.appendChild(pageItem);
                        }

                        logMessage.textContent = "Showing the latest 30 logs";
                    } else {
                        alert(logs.message || "Failed to fetch logs");
                    }
                };

                fetchLogs(currentPage);

                const logModal = new bootstrap.Modal(document.getElementById("logModal"));
                logModal.show();
            }
        });
    });

    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    sidebarToggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });
});
