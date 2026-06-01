
/**
 * Volunteer Dashboard Interactivity
 */

// Section Switching Logic
function showSection(sectionId, element) {
    const activeSection = document.getElementById(sectionId);
    if (!activeSection) return;

    // Check if already active to prevent redundant animations
    if (activeSection.style.display === 'block') return;

    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the targeted section
    activeSection.style.display = 'block';
    
    // Reset main content scroll position smoothly
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.scrollTop = 0;
    }

    // Update active state in sidebar
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.classList.remove('active');
    });
    
    if (element) {
        element.classList.add('active');
    }
}

// Mark Task as Complete
function markComplete(btn, taskId = null) {
    const taskItem = btn.parentElement;
    const taskInfo = taskItem.querySelector('.task-info');
    
    const makeChange = () => {
        // Create status badge
        const badge = document.createElement('span');
        badge.className = 'status-badge status-completed';
        badge.innerText = 'Completed';
        
        // Replace button with badge
        btn.remove();
        taskItem.appendChild(badge);
        
        // Visual feedback
        taskItem.style.opacity = '0.7';
        taskItem.style.background = 'var(--surface)';

        // Update task progress bar
        updateTaskProgress();
    };

    if (taskId) {
        const formData = new FormData();
        formData.append('task_id', taskId);

        fetch('update_task_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                makeChange();
            } else {
                alert('Error updating task in database: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error updating task:', err);
            // Fallback for static html mockup execution
            makeChange();
        });
    } else {
        makeChange();
    }
}

// Chat Functionality
function sendMessage() {
    const input = document.getElementById('chatInput');
    const container = document.getElementById('chatMessages');
    
    if (input.value.trim() === '') return;
    
    // Create message element
    const msgDiv = document.createElement('div');
    msgDiv.className = 'message sent';
    msgDiv.innerText = input.value;
    
    // Add to container
    container.appendChild(msgDiv);
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
    
    // Clear input
    input.value = '';
    
    // Mock response from manager
    setTimeout(() => {
        const responseDiv = document.createElement('div');
        responseDiv.className = 'message received';
        responseDiv.innerText = "Received. I'll check that immediately.";
        container.appendChild(responseDiv);
        container.scrollTop = container.scrollHeight;
    }, 1500);
}

// Handle Hash and Initial Load
document.addEventListener('DOMContentLoaded', () => {
    // Check for hash in URL (e.g., #tasks)
    const hash = window.location.hash.substring(1);
    if (hash) {
        const navItem = document.querySelector(`.nav-item[onclick*="'${hash}'"]`);
        if (navItem) {
            showSection(hash, navItem);
        }
    }

    // Chat Enter key listener
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // Load and render supplies and issue logs
    renderSupplyLogs();
    renderIssueLogs();
    
    // Calculate and update task progress bar
    updateTaskProgress();
});

// Handle browser back/forward buttons
window.addEventListener('hashchange', () => {
    const hash = window.location.hash.substring(1);
    if (hash) {
        const navItem = document.querySelector(`.nav-item[onclick*="'${hash}'"]`);
        if (navItem) {
            showSection(hash, navItem);
        }
    }
});

// Supplies Logging Logic
function logSupply() {
    const itemNameInput = document.getElementById('supplyItemName');
    const quantityInput = document.getElementById('supplyQuantity');
    const recipientInput = document.getElementById('supplyRecipient');
    
    const itemName = itemNameInput.value.trim();
    const quantity = quantityInput.value.trim();
    const recipient = recipientInput.value.trim();
    
    if (!itemName || !quantity || !recipient) return;
    
    const submitBtn = document.querySelector('#suppliesForm button[type="submit"]');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('item_name', itemName);
    formData.append('quantity', quantity);
    formData.append('recipient_name', recipient);
    
    fetch('save_supply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            renderSupplyLogs();
            
            // Clear inputs
            itemNameInput.value = '';
            quantityInput.value = '';
            recipientInput.value = '';
            
            // Feedback
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Recorded!';
                submitBtn.style.background = 'var(--emerald)';
                submitBtn.style.borderColor = 'var(--emerald)';
                submitBtn.style.color = '#fff';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.style.background = '';
                    submitBtn.style.borderColor = '';
                    submitBtn.style.color = '';
                    submitBtn.disabled = false;
                }, 1500);
            }
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        console.warn('Database save failed, falling back to LocalStorage', err);
        
        let supplyLogs = JSON.parse(localStorage.getItem('supplyLogs')) || [];
        const newLog = {
            id: Date.now(),
            itemName: itemName,
            quantity: quantity,
            recipient: recipient,
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            date: new Date().toLocaleDateString()
        };
        supplyLogs.unshift(newLog);
        localStorage.setItem('supplyLogs', JSON.stringify(supplyLogs));
        
        renderSupplyLogs();
        
        // Clear inputs
        itemNameInput.value = '';
        quantityInput.value = '';
        recipientInput.value = '';
        
        // Feedback
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Recorded (Local)!';
            submitBtn.style.background = 'var(--emerald)';
            submitBtn.style.borderColor = 'var(--emerald)';
            submitBtn.style.color = '#fff';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.style.background = '';
                submitBtn.style.borderColor = '';
                submitBtn.style.color = '';
                submitBtn.disabled = false;
            }, 1500);
        }
    });
}

function renderSupplyLogs() {
    const listContainer = document.getElementById('loggedSuppliesList');
    if (!listContainer) return;
    
    fetch('get_supplies.php')
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            const supplyLogs = res.data;
            listContainer.innerHTML = '';
            
            if (supplyLogs.length === 0) {
                listContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: var(--muted);">
                        <i class="fa-solid fa-box-open" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p style="font-size: 14px;">No supply logs recorded yet. Use the form on the left to start logging distribution.</p>
                    </div>
                `;
                return;
            }
            
            supplyLogs.forEach(log => {
                const itemElement = document.createElement('div');
                itemElement.className = 'supply-log-item';
                
                let iconClass = 'fa-box';
                const nameLower = log.itemName.toLowerCase();
                if (nameLower.includes('water') || nameLower.includes('drinking') || nameLower.includes('saline')) {
                    iconClass = 'fa-faucet-drip';
                } else if (nameLower.includes('rice') || nameLower.includes('food') || nameLower.includes('bread') || nameLower.includes('dry') || nameLower.includes('snack')) {
                    iconClass = 'fa-bowl-rice';
                } else if (nameLower.includes('medicine') || nameLower.includes('aid') || nameLower.includes('first aid') || nameLower.includes('tablet') || nameLower.includes('health')) {
                    iconClass = 'fa-kit-medical';
                } else if (nameLower.includes('dress') || nameLower.includes('cloth') || nameLower.includes('blanket') || nameLower.includes('shirt')) {
                    iconClass = 'fa-shirt';
                }
                
                itemElement.innerHTML = `
                    <div class="supply-log-icon">
                        <i class="fa-solid ${iconClass}"></i>
                    </div>
                    <div class="supply-log-info">
                        <h4>${log.itemName}</h4>
                        <p>Qty: <strong>${log.quantity}</strong> • Recipient: <strong>${log.recipient}</strong></p>
                    </div>
                    <div class="supply-log-time">
                        <strong>${log.timestamp}</strong><br><span style="font-size: 9px; opacity: 0.7;">${log.date}</span>
                    </div>
                `;
                listContainer.appendChild(itemElement);
            });
        }
    })
    .catch(err => {
        console.warn('Could not load supplies from DB, falling back to LocalStorage', err);
        renderSupplyLogsFromLocalStorage();
    });
}

function renderSupplyLogsFromLocalStorage() {
    const listContainer = document.getElementById('loggedSuppliesList');
    if (!listContainer) return;
    
    let supplyLogs = JSON.parse(localStorage.getItem('supplyLogs')) || [];
    listContainer.innerHTML = '';
    
    if (supplyLogs.length === 0) {
        listContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: var(--muted);">
                <i class="fa-solid fa-box-open" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                <p style="font-size: 14px;">No supply logs recorded yet. Use the form on the left to start logging distribution.</p>
            </div>
        `;
        return;
    }
    
    supplyLogs.forEach(log => {
        const itemElement = document.createElement('div');
        itemElement.className = 'supply-log-item';
        
        let iconClass = 'fa-box';
        const nameLower = log.itemName.toLowerCase();
        if (nameLower.includes('water') || nameLower.includes('drinking') || nameLower.includes('saline')) {
            iconClass = 'fa-faucet-drip';
        } else if (nameLower.includes('rice') || nameLower.includes('food') || nameLower.includes('bread') || nameLower.includes('dry') || nameLower.includes('snack')) {
            iconClass = 'fa-bowl-rice';
        } else if (nameLower.includes('medicine') || nameLower.includes('aid') || nameLower.includes('first aid') || nameLower.includes('tablet') || nameLower.includes('health')) {
            iconClass = 'fa-kit-medical';
        } else if (nameLower.includes('dress') || nameLower.includes('cloth') || nameLower.includes('blanket') || nameLower.includes('shirt')) {
            iconClass = 'fa-shirt';
        }
        
        itemElement.innerHTML = `
            <div class="supply-log-icon">
                <i class="fa-solid ${iconClass}"></i>
            </div>
            <div class="supply-log-info">
                <h4>${log.itemName}</h4>
                <p>Qty: <strong>${log.quantity}</strong> • Recipient: <strong>${log.recipient}</strong></p>
            </div>
            <div class="supply-log-time">
                <strong>${log.timestamp}</strong><br><span style="font-size: 9px; opacity: 0.7;">${log.date}</span>
            </div>
        `;
        listContainer.appendChild(itemElement);
    });
}

function clearSupplyLogs() {
    if (confirm("Are you sure you want to clear your supply logs?")) {
        localStorage.setItem('supplyLogs', JSON.stringify([]));
        renderSupplyLogs();
    }
}

// Field Issues Logging Logic
function logIssue() {
    const typeSelect = document.getElementById('issueType');
    const descriptionInput = document.getElementById('issueDescription');
    const locationInput = document.getElementById('issueLocation');
    
    const issueType = typeSelect.value;
    const description = descriptionInput.value.trim();
    const location = locationInput.value.trim();
    
    if (!issueType || !description || !location) return;
    
    const submitBtn = document.querySelector('#reportsForm button[type="submit"]');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('issue_type', issueType);
    formData.append('description', description);
    formData.append('location', location);
    
    fetch('save_issue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            renderIssueLogs();
            
            // Clear inputs
            descriptionInput.value = '';
            locationInput.value = '';
            
            // Feedback
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Report Submitted!';
                submitBtn.style.background = 'var(--emerald)';
                submitBtn.style.borderColor = 'var(--emerald)';
                submitBtn.style.color = '#fff';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.style.background = '';
                    submitBtn.style.borderColor = '';
                    submitBtn.style.color = '';
                    submitBtn.disabled = false;
                }, 1500);
            }
        } else {
            alert('Error: ' + res.message);
        }
    })
    .catch(err => {
        console.warn('Database save failed, falling back to LocalStorage', err);
        
        let issueLogs = JSON.parse(localStorage.getItem('issueLogs')) || [];
        const newLog = {
            id: Date.now(),
            type: issueType,
            description: description,
            location: location,
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            date: new Date().toLocaleDateString()
        };
        issueLogs.unshift(newLog);
        localStorage.setItem('issueLogs', JSON.stringify(issueLogs));
        
        renderIssueLogs();
        
        // Clear inputs
        descriptionInput.value = '';
        locationInput.value = '';
        
        // Feedback
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Report Submitted (Local)!';
            submitBtn.style.background = 'var(--emerald)';
            submitBtn.style.borderColor = 'var(--emerald)';
            submitBtn.style.color = '#fff';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.style.background = '';
                submitBtn.style.borderColor = '';
                submitBtn.style.color = '';
                submitBtn.disabled = false;
            }, 1500);
        }
    });
}

function renderIssueLogs() {
    const listContainer = document.getElementById('loggedReportsList');
    if (!listContainer) return;
    
    fetch('get_issues.php')
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            const issueLogs = res.data;
            listContainer.innerHTML = '';
            
            if (issueLogs.length === 0) {
                listContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: var(--muted);">
                        <i class="fa-solid fa-circle-check" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5; color: var(--emerald);"></i>
                        <p style="font-size: 14px;">All clear! No field issues reported yet. Use the form on the left to report issues.</p>
                    </div>
                `;
                return;
            }
            
            issueLogs.forEach(log => {
                const itemElement = document.createElement('div');
                itemElement.className = 'report-log-item';
                
                let iconClass = 'fa-triangle-exclamation';
                if (log.type.includes('Medical')) {
                    iconClass = 'fa-square-h';
                } else if (log.type.includes('Supply')) {
                    iconClass = 'fa-box-open';
                } else if (log.type.includes('Infrastructure') || log.type.includes('Damage')) {
                    iconClass = 'fa-hammer';
                }
                
                itemElement.innerHTML = `
                    <div class="report-log-icon">
                        <i class="fa-solid ${iconClass}"></i>
                    </div>
                    <div class="report-log-info">
                        <h4>${log.type}</h4>
                        <p>${log.description}</p>
                        <div class="report-log-loc"><i class="fa-solid fa-location-dot" style="margin-right: 4px; font-size: 10px;"></i>${log.location}</div>
                    </div>
                    <div class="report-log-time">
                        <strong>${log.timestamp}</strong><br><span style="font-size: 9px; opacity: 0.7;">${log.date}</span>
                    </div>
                `;
                listContainer.appendChild(itemElement);
            });
        }
    })
    .catch(err => {
        console.warn('Could not load issues from DB, falling back to LocalStorage', err);
        renderIssueLogsFromLocalStorage();
    });
}

function renderIssueLogsFromLocalStorage() {
    const listContainer = document.getElementById('loggedReportsList');
    if (!listContainer) return;
    
    let issueLogs = JSON.parse(localStorage.getItem('issueLogs')) || [];
    listContainer.innerHTML = '';
    
    if (issueLogs.length === 0) {
        listContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: var(--muted);">
                <i class="fa-solid fa-circle-check" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5; color: var(--emerald);"></i>
                <p style="font-size: 14px;">All clear! No field issues reported yet. Use the form on the left to report issues.</p>
            </div>
        `;
        return;
    }
    
    issueLogs.forEach(log => {
        const itemElement = document.createElement('div');
        itemElement.className = 'report-log-item';
        
        let iconClass = 'fa-triangle-exclamation';
        if (log.type.includes('Medical')) {
            iconClass = 'fa-square-h';
        } else if (log.type.includes('Supply')) {
            iconClass = 'fa-box-open';
        } else if (log.type.includes('Infrastructure') || log.type.includes('Damage')) {
            iconClass = 'fa-hammer';
        }
        
        itemElement.innerHTML = `
            <div class="report-log-icon">
                <i class="fa-solid ${iconClass}"></i>
            </div>
            <div class="report-log-info">
                <h4>${log.type}</h4>
                <p>${log.description}</p>
                <div class="report-log-loc"><i class="fa-solid fa-location-dot" style="margin-right: 4px; font-size: 10px;"></i>${log.location}</div>
            </div>
            <div class="report-log-time">
                <strong>${log.timestamp}</strong><br><span style="font-size: 9px; opacity: 0.7;">${log.date}</span>
            </div>
        `;
        listContainer.appendChild(itemElement);
    });
}

function clearIssueLogs() {
    if (confirm("Are you sure you want to clear the reported issues log?")) {
        localStorage.setItem('issueLogs', JSON.stringify([]));
        renderIssueLogs();
    }
}

// Calculate and update task progress
function updateTaskProgress() {
    const taskList = document.querySelector('#tasks .task-list');
    if (!taskList) return;
    
    const taskItems = taskList.querySelectorAll('.task-item');
    const totalTasks = taskItems.length;
    
    if (totalTasks === 0) return;
    
    let completedTasks = 0;
    taskItems.forEach(item => {
        const badge = item.querySelector('.status-completed');
        if (badge) {
            completedTasks++;
        }
    });
    
    const percentage = Math.round((completedTasks / totalTasks) * 100);
    
    const percentEl = document.getElementById('taskProgressPercent');
    const barEl = document.getElementById('taskProgressBar');
    const statsEl = document.getElementById('taskProgressStats');
    
    if (percentEl) percentEl.innerText = `${percentage}%`;
    if (barEl) barEl.style.width = `${percentage}%`;
    if (statsEl) statsEl.innerText = `${completedTasks} of ${totalTasks} tasks completed`;
}
