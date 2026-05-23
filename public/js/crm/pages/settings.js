(() => {
            const settings = document.querySelector('.calling-crm-settings');
            const tabsTrack = settings?.querySelector('.tabs-track');
            const tabArrows = settings?.querySelectorAll('.tab-arrow');

            if (!tabsTrack || tabArrows?.length !== 2) return;

            const [previousArrow, nextArrow] = tabArrows;
            const tabButtons = settings.querySelectorAll('[data-settings-tab]');
            const tabPanels = settings.querySelectorAll('[data-settings-panel]');
            const addUserModal = settings.querySelector('[data-add-user-modal]');
            const modalBackdrops = settings.querySelectorAll('.crm-backdrop');
            const addUserForm = settings.querySelector('[data-add-user-form]');
            const userRows = settings.querySelector('[data-user-rows]');
            const usersTableBody = settings.querySelector('[data-users-table-body]');
            const userActionsMenu = settings.querySelector('[data-user-actions-menu]');
            const campaignDetailsModal = settings.querySelector('[data-campaign-details-modal]');
            const campaignUserName = settings.querySelector('[data-campaign-user-name]');
            const usersFlowViews = settings.querySelectorAll('[data-users-flow]');
            const reassignCampaignBody = settings.querySelector('[data-reassign-campaign-body]');
            const reassignLeadsBody = settings.querySelector('[data-reassign-leads-body]');
            const summaryTitleCampaign = settings.querySelector('[data-summary-title-campaign]');
            const pipelineModal = settings.querySelector('[data-pipeline-modal]');
            const pipelineForm = settings.querySelector('[data-pipeline-form]');
            const pipelineSelect = settings.querySelector('[data-pipeline-select]');
            const pipelineNameInput = settings.querySelector('[data-pipeline-name-input]');
            const pipelineModalTitle = settings.querySelector('[data-pipeline-modal-title]');
            const pipelineSubmit = settings.querySelector('[data-pipeline-submit]');
            const stageNameInput = settings.querySelector('[data-stage-name-input]');
            const stageTagsField = settings.querySelector('[data-stage-tags-field]');
            const stageSave = settings.querySelector('[data-stage-save]');
            const profileForm = settings.querySelector('[data-profile-form]');
            const profileAddress = settings.querySelector('[data-profile-address]');
            const profileAddressCount = settings.querySelector('[data-profile-address-count]');
            const workingHoursPanel = settings.querySelector('[data-working-hours-panel]');
            const retryTable = settings.querySelector('[data-retry-table]');
            const retryMenu = settings.querySelector('[data-retry-menu]');
            const retryLogicModal = settings.querySelector('[data-retry-logic-modal]');
            const retryLogicForm = settings.querySelector('[data-retry-logic-form]');
            const retryReasonModal = settings.querySelector('[data-retry-reason-modal]');
            const retryReasonForm = settings.querySelector('[data-retry-reason-form]');
            const retryReasonInput = settings.querySelector('[data-retry-reason-input]');
            const retryCount = settings.querySelector('[data-retry-count]');
            const retryInterval = settings.querySelector('[data-retry-interval]');
            const retryUnit = settings.querySelector('[data-retry-unit]');
            const priorityList = settings.querySelector('[data-priority-list]');
            const propertyTableBody = settings.querySelector('[data-property-table-body]');
            const propertyCount = settings.querySelector('[data-property-count]');
            const propertyModal = settings.querySelector('[data-property-modal]');
            const propertyForm = settings.querySelector('[data-property-form]');
            const propertyModalTitle = settings.querySelector('[data-property-modal-title]');
            const propertyNameInput = settings.querySelector('[data-property-name-input]');
            const propertyNameCount = settings.querySelector('[data-property-name-count]');
            const propertyTypeInput = settings.querySelector('[data-property-type-input]');
            let activeUserActionButton = null;
            let editingUserRow = null;
            let pipelineModalMode = 'create';
            let activeRetryRow = null;
            let draggingPriorityItem = null;
            let priorityDragGhost = null;
            let activePropertyRow = null;

            if (window.callingCrmRequest) {
                const scrollStep = () => Math.max(220, Math.round(tabsTrack.clientWidth * .72));
                const updateTabArrows = () => {
                    const scrollEnd = tabsTrack.scrollWidth - tabsTrack.clientWidth;
                    previousArrow.disabled = tabsTrack.scrollLeft <= 1;
                    nextArrow.disabled = tabsTrack.scrollLeft >= scrollEnd - 1;
                };
                const setScrollLock = () => {
                    const modalOpen = [...modalBackdrops].some(backdrop => backdrop.classList.contains('open'));
                    document.body.style.overflow = modalOpen ? 'hidden' : '';
                };
                const closeBackdrop = backdrop => {
                    backdrop?.classList.remove('open');
                    backdrop?.setAttribute('aria-hidden', 'true');
                    setScrollLock();
                };
                const openBackdrop = backdrop => {
                    backdrop?.classList.add('open');
                    backdrop?.setAttribute('aria-hidden', 'false');
                    setScrollLock();
                };
                const setUsersFlow = flowName => {
                    usersFlowViews.forEach(view => view.classList.toggle('active', view.dataset.usersFlow === flowName));
                };
                const setPanel = panelName => {
                    const panel = settings.querySelector(`[data-settings-panel="${panelName}"]`);
                    if (!panel) return;
                    tabButtons.forEach(button => button.classList.toggle('active', button.dataset.settingsTab === panelName));
                    tabPanels.forEach(tabPanel => tabPanel.classList.toggle('active', tabPanel === panel));
                    if (panelName === 'users') setUsersFlow('list');
                    userActionsMenu?.classList.remove('open');
                };
                const resetUserRow = row => {
                    row?.querySelectorAll('input').forEach(input => {
                        input.value = '';
                        input.required = input.name === 'password[]' || input.hasAttribute('required');
                    });
                    row?.querySelectorAll('select').forEach(select => select.value = '');
                    const password = row?.querySelector('input[name="password[]"]');
                    const icon = row?.querySelector('[data-password-toggle] i');
                    if (password) password.type = 'password';
                    if (icon) icon.className = 'fa-solid fa-eye-slash';
                };
                const fillUserFormForEdit = row => {
                    const formRow = userRows?.querySelector('[data-user-form-row]');
                    if (!formRow || !row) return;
                    while (userRows.children.length > 1) userRows.lastElementChild.remove();
                    resetUserRow(formRow);
                    formRow.querySelector('input[name="name[]"]').value = row.cells[1]?.textContent.trim() || '';
                    formRow.querySelector('input[name="number[]"]').value = row.cells[2]?.textContent.trim() || '';
                    formRow.querySelector('input[name="email[]"]').value = row.cells[4]?.textContent.trim() || '';
                    formRow.querySelector('select[name="role[]"]').value = row.dataset.crmUserRole || 'agent';
                    const password = formRow.querySelector('input[name="password[]"]');
                    if (password) {
                        password.value = '';
                        password.required = false;
                    }
                };
                const openUserModal = row => {
                    editingUserRow = row || null;
                    if (addUserForm) addUserForm._crmEditRow = row || null;
                    if (row) {
                        fillUserFormForEdit(row);
                    } else {
                        userRows?.querySelectorAll('[data-user-form-row]').forEach((formRow, index) => {
                            if (index) formRow.remove();
                            else {
                                resetUserRow(formRow);
                                const password = formRow.querySelector('input[name="password[]"]');
                                if (password) password.required = true;
                            }
                        });
                    }
                    openBackdrop(addUserModal);
                    addUserModal?.querySelector('input[name="name[]"]')?.focus();
                };
                const openUserActions = button => {
                    if (!userActionsMenu) return;
                    activeUserActionButton = button;
                    const rect = button.getBoundingClientRect();
                    const width = 260;
                    const height = 383;
                    userActionsMenu.style.left = `${Math.min(Math.max(4, rect.left - width + rect.width), window.innerWidth - width - 8)}px`;
                    userActionsMenu.style.top = `${Math.min(Math.max(8, rect.bottom - 10), window.innerHeight - height - 8)}px`;
                    userActionsMenu.classList.add('open');
                };
                const openPropertyModal = row => {
                    activePropertyRow = row || null;
                    if (propertyModalTitle) propertyModalTitle.textContent = row ? 'Edit Custom Column' : 'Add Custom Property';
                    if (propertyNameInput) propertyNameInput.value = row?.querySelector('[data-property-name]')?.textContent.trim() || '';
                    if (propertyTypeInput) {
                        propertyTypeInput.disabled = Boolean(row);
                        propertyTypeInput.value = row?.querySelector('[data-property-type]')?.textContent.trim() || '';
                    }
                    if (propertyNameCount) propertyNameCount.textContent = `${propertyNameInput?.value.length || 0}/60`;
                    openBackdrop(propertyModal);
                    propertyNameInput?.focus();
                };
                const openPipelineModal = mode => {
                    const isEdit = mode === 'edit';
                    if (pipelineModalTitle) pipelineModalTitle.textContent = isEdit ? 'Edit Pipeline' : 'Add Pipeline';
                    if (pipelineSubmit) pipelineSubmit.textContent = isEdit ? 'Update' : 'Create';
                    if (pipelineNameInput) pipelineNameInput.value = isEdit ? pipelineSelect?.selectedOptions[0]?.textContent.trim() || '' : '';
                    openBackdrop(pipelineModal);
                    pipelineNameInput?.focus();
                };
                const openRetryMenu = (button, row) => {
                    if (!retryMenu) return;
                    activeRetryRow = row;
                    const rect = button.getBoundingClientRect();
                    retryMenu.style.left = `${Math.min(Math.max(8, rect.right - 92), window.innerWidth - 112)}px`;
                    retryMenu.style.top = `${Math.min(Math.max(8, rect.bottom - 6), window.innerHeight - 98)}px`;
                    retryMenu.classList.add('open');
                };
                const updateAddressCount = () => {
                    if (profileAddress && profileAddressCount) profileAddressCount.textContent = `${profileAddress.value.length}/250`;
                };

                previousArrow.addEventListener('click', () => tabsTrack.scrollBy({ left: -scrollStep(), behavior: 'smooth' }));
                nextArrow.addEventListener('click', () => tabsTrack.scrollBy({ left: scrollStep(), behavior: 'smooth' }));
                tabsTrack.addEventListener('scroll', updateTabArrows, { passive: true });
                window.addEventListener('resize', updateTabArrows);
                updateTabArrows();
                tabButtons.forEach(button => button.addEventListener('click', () => setPanel(button.dataset.settingsTab)));

                settings.querySelector('[data-add-user-open]')?.addEventListener('click', () => openUserModal());
                settings.querySelector('[data-add-user-close]')?.addEventListener('click', () => closeBackdrop(addUserModal));
                settings.querySelector('[data-add-user-row]')?.addEventListener('click', () => {
                    const source = userRows?.querySelector('[data-user-form-row]');
                    if (!source || !userRows) return;
                    const row = source.cloneNode(true);
                    resetUserRow(row);
                    userRows.append(row);
                });
                userRows?.addEventListener('click', event => {
                    const passwordToggle = event.target.closest('[data-password-toggle]');
                    if (passwordToggle) {
                        const passwordInput = passwordToggle.closest('.password-field')?.querySelector('input');
                        const icon = passwordToggle.querySelector('i');
                        if (!passwordInput || !icon) return;
                        const reveal = passwordInput.type === 'password';
                        passwordInput.type = reveal ? 'text' : 'password';
                        icon.className = reveal ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                    }
                    const removeButton = event.target.closest('[data-remove-user-row]');
                    if (removeButton) {
                        const rows = userRows.querySelectorAll('[data-user-form-row]');
                        if (rows.length > 1) removeButton.closest('[data-user-form-row]')?.remove();
                        else resetUserRow(rows[0]);
                    }
                });
                usersTableBody?.addEventListener('click', event => {
                    const toggle = event.target.closest('[data-user-actions-toggle]');
                    if (!toggle) return;
                    event.stopPropagation();
                    if (toggle === activeUserActionButton && userActionsMenu?.classList.contains('open')) {
                        userActionsMenu.classList.remove('open');
                    } else {
                        openUserActions(toggle);
                    }
                });
                userActionsMenu?.addEventListener('click', event => {
                    const action = event.target.closest('[data-user-action]')?.dataset.userAction;
                    const row = activeUserActionButton?.closest('tr');
                    if (action === 'edit' || action === 'password') openUserModal(row);
                    if (action === 'campaigns') {
                        if (campaignUserName) campaignUserName.textContent = row?.cells[1]?.textContent.trim() || 'User';
                        openBackdrop(campaignDetailsModal);
                    }
                    if (action === 'reassign') setUsersFlow('reassign-campaigns');
                    userActionsMenu.classList.remove('open');
                });
                settings.querySelector('[data-campaign-details-close]')?.addEventListener('click', () => closeBackdrop(campaignDetailsModal));
                settings.querySelector('[data-reassign-back]')?.addEventListener('click', () => setUsersFlow('list'));
                settings.querySelector('[data-summary-back]')?.addEventListener('click', () => setUsersFlow('reassign-campaigns'));
                settings.querySelectorAll('[data-reassign-summary-open]').forEach(button => {
                    button.addEventListener('click', () => setUsersFlow('reassign-summary'));
                });
                settings.querySelectorAll('[data-pipeline-modal-open]').forEach(button => {
                    button.addEventListener('click', () => openPipelineModal(button.dataset.pipelineModalOpen));
                });
                settings.querySelector('[data-pipeline-modal-close]')?.addEventListener('click', () => closeBackdrop(pipelineModal));
                settings.querySelector('[data-working-hours-toggle]')?.addEventListener('click', () => workingHoursPanel?.classList.toggle('open'));
                profileAddress?.addEventListener('input', updateAddressCount);
                updateAddressCount();
                retryTable?.addEventListener('click', event => {
                    const setup = event.target.closest('[data-retry-setup]');
                    if (setup) openBackdrop(retryLogicModal);
                    const menuToggle = event.target.closest('[data-retry-action-toggle]');
                    if (menuToggle) openRetryMenu(menuToggle, menuToggle.closest('[data-retry-row]'));
                });
                settings.querySelector('[data-retry-add]')?.addEventListener('click', () => {
                    activeRetryRow = null;
                    if (retryReasonInput) retryReasonInput.value = '';
                    openBackdrop(retryReasonModal);
                });
                retryMenu?.addEventListener('click', event => {
                    if (event.target.closest('[data-retry-menu-action]')?.dataset.retryMenuAction === 'edit') {
                        if (retryReasonInput) retryReasonInput.value = activeRetryRow?.querySelector('.retry-reason')?.textContent.trim() || '';
                        openBackdrop(retryReasonModal);
                    }
                    retryMenu.classList.remove('open');
                });
                settings.querySelector('[data-retry-logic-close]')?.addEventListener('click', () => closeBackdrop(retryLogicModal));
                settings.querySelector('[data-retry-reason-close]')?.addEventListener('click', () => closeBackdrop(retryReasonModal));
                settings.querySelector('[data-property-add]')?.addEventListener('click', () => openPropertyModal());
                settings.querySelector('[data-property-close]')?.addEventListener('click', () => closeBackdrop(propertyModal));
                propertyTableBody?.addEventListener('click', event => {
                    const row = event.target.closest('[data-property-row]');
                    if (event.target.closest('[data-property-edit]')) openPropertyModal(row);
                });
                propertyNameInput?.addEventListener('input', () => {
                    if (propertyNameCount) propertyNameCount.textContent = `${propertyNameInput.value.length}/60`;
                });
                modalBackdrops.forEach(backdrop => {
                    backdrop.addEventListener('click', event => {
                        if (event.target === backdrop) closeBackdrop(backdrop);
                    });
                });
                document.addEventListener('click', event => {
                    if (!event.target.closest('[data-user-actions-toggle]') && !event.target.closest('[data-user-actions-menu]')) {
                        userActionsMenu?.classList.remove('open');
                    }
                    if (!event.target.closest('[data-retry-action-toggle]') && !event.target.closest('[data-retry-menu]')) {
                        retryMenu?.classList.remove('open');
                    }
                });
                document.addEventListener('keydown', event => {
                    if (event.key !== 'Escape') return;
                    userActionsMenu?.classList.remove('open');
                    retryMenu?.classList.remove('open');
                    modalBackdrops.forEach(closeBackdrop);
                });
                const modalPortal = document.createElement('div');
                modalPortal.className = 'calling-crm-settings crm-modal-portal';
                document.body.append(modalPortal);
                modalBackdrops.forEach(backdrop => modalPortal.append(backdrop));
                return;
            }

            const scrollStep = () => Math.max(220, Math.round(tabsTrack.clientWidth * .72));

            const updateTabArrows = () => {
                const scrollEnd = tabsTrack.scrollWidth - tabsTrack.clientWidth;
                previousArrow.disabled = tabsTrack.scrollLeft <= 1;
                nextArrow.disabled = tabsTrack.scrollLeft >= scrollEnd - 1;
            };

            previousArrow.addEventListener('click', () => {
                tabsTrack.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
            });

            nextArrow.addEventListener('click', () => {
                tabsTrack.scrollBy({ left: scrollStep(), behavior: 'smooth' });
            });

            tabsTrack.addEventListener('scroll', updateTabArrows, { passive: true });
            window.addEventListener('resize', updateTabArrows);
            updateTabArrows();

            const setScrollLock = () => {
                const modalOpen = addUserModal?.classList.contains('open')
                    || campaignDetailsModal?.classList.contains('open')
                    || pipelineModal?.classList.contains('open')
                    || retryLogicModal?.classList.contains('open')
                    || retryReasonModal?.classList.contains('open')
                    || propertyModal?.classList.contains('open');
                document.body.style.overflow = modalOpen ? 'hidden' : '';
            };

            const setUsersFlow = flowName => {
                usersFlowViews.forEach(view => view.classList.toggle('active', view.dataset.usersFlow === flowName));
            };

            const selectSummaryCampaign = campaignName => {
                const currentCampaign = campaignName || 'MP Transacted';
                if (summaryTitleCampaign) summaryTitleCampaign.textContent = currentCampaign;
                settings.querySelectorAll('[data-summary-campaign-card]').forEach(card => {
                    card.classList.toggle('active', card.dataset.summaryCampaignCard === currentCampaign);
                });
            };

            const openReassignSummary = campaignName => {
                selectSummaryCampaign(campaignName);
                setUsersFlow('reassign-summary');
            };

            const renumberUsers = () => {
                usersTableBody?.querySelectorAll('tr').forEach((row, index) => {
                    row.cells[0].textContent = String(index + 1);
                });
            };

            const makeUserActionButton = name => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'dots-btn';
                button.dataset.userActionsToggle = '';
                button.dataset.userName = name;
                button.setAttribute('aria-label', 'User actions');
                button.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';
                return button;
            };

            const resetUserRow = row => {
                row.querySelectorAll('input').forEach(input => input.value = '');
                row.querySelectorAll('select').forEach(select => select.value = '');
                const password = row.querySelector('input[name="password[]"]');
                const icon = row.querySelector('[data-password-toggle] i');
                if (password) password.type = 'password';
                if (icon) icon.className = 'fa-solid fa-eye-slash';
            };

            const addBlankUserRow = () => {
                const source = userRows?.querySelector('[data-user-form-row]');
                if (!source || !userRows) return;
                const row = source.cloneNode(true);
                resetUserRow(row);
                userRows.append(row);
            };

            const fillUserFormForEdit = row => {
                const formRow = userRows?.querySelector('[data-user-form-row]');
                if (!formRow || !row) return;
                while (userRows.children.length > 1) userRows.lastElementChild.remove();
                resetUserRow(formRow);
                formRow.querySelector('input[name="name[]"]').value = row.cells[1].textContent.trim();
                formRow.querySelector('input[name="number[]"]').value = row.cells[2].textContent.trim();
                formRow.querySelector('select[name="role[]"]').value = row.cells[5].textContent.trim();
                formRow.querySelector('input[name="email[]"]').value = row.cells[4].textContent.trim();
                formRow.querySelector('input[name="password[]"]').required = false;
            };

            const openUserModal = row => {
                editingUserRow = row || null;
                if (row) {
                    fillUserFormForEdit(row);
                } else {
                    userRows?.querySelectorAll('[data-user-form-row]').forEach((formRow, index) => {
                        if (index) formRow.remove();
                        else {
                            resetUserRow(formRow);
                            formRow.querySelector('input[name="password[]"]').required = true;
                        }
                    });
                }
                addUserModal?.classList.add('open');
                addUserModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                addUserModal?.querySelector('input[name="name[]"]')?.focus();
            };

            const closeUserModal = () => {
                editingUserRow = null;
                addUserModal?.classList.remove('open');
                addUserModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const openCampaignDetailsModal = row => {
                if (campaignUserName) campaignUserName.textContent = row?.cells[1]?.textContent.trim() || 'User';
                campaignDetailsModal?.classList.add('open');
                campaignDetailsModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
            };

            const closeCampaignDetailsModal = () => {
                campaignDetailsModal?.classList.remove('open');
                campaignDetailsModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const appendUserRow = formRow => {
                if (!usersTableBody) return;
                const values = {
                    name: formRow.querySelector('input[name="name[]"]').value.trim(),
                    number: formRow.querySelector('input[name="number[]"]').value.trim(),
                    email: formRow.querySelector('input[name="email[]"]').value.trim(),
                    role: formRow.querySelector('select[name="role[]"]').value.trim(),
                };
                const row = usersTableBody.insertRow();
                ['', values.name, values.number, '', values.email, values.role, '10-10-2026'].forEach(value => {
                    row.insertCell().textContent = value;
                });
                const statusCell = row.insertCell();
                const status = document.createElement('span');
                status.className = 'status-pill';
                status.textContent = 'Active';
                statusCell.append(status);
                row.insertCell().append(makeUserActionButton(values.name));
            };

            const updateEditedUser = formRow => {
                if (!editingUserRow) return;
                const name = formRow.querySelector('input[name="name[]"]').value.trim();
                editingUserRow.cells[1].textContent = name;
                editingUserRow.cells[2].textContent = formRow.querySelector('input[name="number[]"]').value.trim();
                editingUserRow.cells[4].textContent = formRow.querySelector('input[name="email[]"]').value.trim();
                editingUserRow.cells[5].textContent = formRow.querySelector('select[name="role[]"]').value.trim();
                const actionButton = editingUserRow.querySelector('[data-user-actions-toggle]');
                if (actionButton) actionButton.dataset.userName = name;
            };

            const closeUserActions = () => {
                userActionsMenu?.classList.remove('open');
                activeUserActionButton = null;
            };

            const openUserActions = button => {
                if (!userActionsMenu) return;
                activeUserActionButton = button;
                const rect = button.getBoundingClientRect();
                const width = 260;
                const height = 383;
                const left = Math.min(Math.max(4, rect.left - width + rect.width), window.innerWidth - width - 8);
                const top = Math.min(Math.max(8, rect.bottom - 10), window.innerHeight - height - 8);
                userActionsMenu.style.left = `${left}px`;
                userActionsMenu.style.top = `${top}px`;
                userActionsMenu.classList.add('open');
            };

            const setPanel = panelName => {
                const panel = settings.querySelector(`[data-settings-panel="${panelName}"]`);
                if (!panel) return;
                tabButtons.forEach(button => button.classList.toggle('active', button.dataset.settingsTab === panelName));
                tabPanels.forEach(tabPanel => tabPanel.classList.toggle('active', tabPanel === panel));
                if (panelName === 'users') setUsersFlow('list');
                closeUserActions();
            };

            tabButtons.forEach(button => {
                button.addEventListener('click', () => setPanel(button.dataset.settingsTab));
            });

            settings.querySelector('[data-add-user-open]')?.addEventListener('click', () => openUserModal());
            settings.querySelector('[data-add-user-row]')?.addEventListener('click', addBlankUserRow);
            settings.querySelector('[data-add-user-close]')?.addEventListener('click', closeUserModal);

            addUserModal?.addEventListener('click', event => {
                if (event.target === addUserModal) closeUserModal();
            });

            campaignDetailsModal?.addEventListener('click', event => {
                if (event.target === campaignDetailsModal) closeCampaignDetailsModal();
            });

            settings.querySelector('[data-campaign-details-close]')?.addEventListener('click', closeCampaignDetailsModal);
            settings.querySelector('[data-reassign-back]')?.addEventListener('click', () => setUsersFlow('list'));
            settings.querySelector('[data-summary-back]')?.addEventListener('click', () => setUsersFlow('reassign-campaigns'));

            settings.querySelectorAll('[data-reassign-summary-open]').forEach(button => {
                button.addEventListener('click', () => openReassignSummary(button.dataset.summaryCampaign));
            });

            settings.querySelectorAll('[data-summary-campaign-card]').forEach(card => {
                card.addEventListener('click', () => selectSummaryCampaign(card.dataset.summaryCampaignCard));
            });

            settings.querySelectorAll('[data-campaign-modal-summary]').forEach(button => {
                button.addEventListener('click', () => {
                    closeCampaignDetailsModal();
                    openReassignSummary(button.dataset.campaignModalSummary);
                });
            });

            const filterTableRows = (body, searchValue) => {
                const query = searchValue.trim().toLowerCase();
                body?.querySelectorAll('tr').forEach(row => {
                    row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
                });
            };

            settings.querySelector('[data-reassign-campaign-search]')?.addEventListener('input', event => {
                filterTableRows(reassignCampaignBody, event.target.value);
            });

            settings.querySelector('[data-reassign-lead-search]')?.addEventListener('input', event => {
                filterTableRows(reassignLeadsBody, event.target.value);
            });

            userRows?.addEventListener('click', event => {
                const passwordToggle = event.target.closest('[data-password-toggle]');
                if (passwordToggle) {
                    const passwordInput = passwordToggle.closest('.password-field')?.querySelector('input');
                    const icon = passwordToggle.querySelector('i');
                    if (!passwordInput || !icon) return;
                    const reveal = passwordInput.type === 'password';
                    passwordInput.type = reveal ? 'text' : 'password';
                    icon.className = reveal ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                    return;
                }

                const removeButton = event.target.closest('[data-remove-user-row]');
                if (removeButton) {
                    const rows = userRows.querySelectorAll('[data-user-form-row]');
                    if (rows.length > 1) removeButton.closest('[data-user-form-row]')?.remove();
                    else resetUserRow(rows[0]);
                }
            });

            addUserForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!addUserForm.reportValidity()) return;
                const rows = [...userRows.querySelectorAll('[data-user-form-row]')];
                if (editingUserRow) updateEditedUser(rows[0]);
                else rows.forEach(appendUserRow);
                renumberUsers();
                closeUserModal();
            });

            usersTableBody?.addEventListener('click', event => {
                const toggle = event.target.closest('[data-user-actions-toggle]');
                if (!toggle) return;
                event.stopPropagation();
                if (toggle === activeUserActionButton && userActionsMenu?.classList.contains('open')) closeUserActions();
                else openUserActions(toggle);
            });

            userActionsMenu?.addEventListener('click', event => {
                const action = event.target.closest('[data-user-action]')?.dataset.userAction;
                const row = activeUserActionButton?.closest('tr');
                if (!action || !row) return;

                if (action === 'edit' || action === 'password') openUserModal(row);
                if (action === 'campaigns') openCampaignDetailsModal(row);
                if (action === 'reassign') setUsersFlow('reassign-campaigns');
                if (action === 'deactivate' || action === 'disable') {
                    const status = row.querySelector('.status-pill');
                    if (status) {
                        status.textContent = status.textContent.trim() === 'Active' ? 'Inactive' : 'Active';
                    }
                }
                if (action === 'delete') {
                    row.remove();
                    renumberUsers();
                }
                closeUserActions();
            });

            const renderStageTags = tags => {
                if (!stageTagsField) return;
                stageTagsField.querySelectorAll('.tag-chip').forEach(chip => chip.remove());
                const tagInput = stageTagsField.querySelector('.tag-input');
                tags.forEach(tag => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'tag-chip';
                    chip.textContent = tag;
                    stageTagsField.insertBefore(chip, tagInput);
                });
            };

            // Mock stage event listeners removed as they are now handled by api-bindings.blade.php


            const openPipelineModal = mode => {
                pipelineModalMode = mode;
                const isEdit = mode === 'edit';
                if (pipelineModalTitle) pipelineModalTitle.textContent = isEdit ? 'Edit Pipeline' : 'Add Pipeline';
                if (pipelineSubmit) pipelineSubmit.textContent = isEdit ? 'Update' : 'Create';
                if (pipelineNameInput) pipelineNameInput.value = isEdit ? pipelineSelect?.selectedOptions[0]?.textContent.trim() || '' : 'New Pipeline';
                pipelineModal?.classList.add('open');
                pipelineModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                pipelineNameInput?.focus();
            };

            settings.querySelectorAll('[data-pipeline-modal-open]').forEach(button => {
                button.addEventListener('click', () => openPipelineModal(button.dataset.pipelineModalOpen));
            });

            const closePipelineModal = () => {
                pipelineModal?.classList.remove('open');
                pipelineModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            settings.querySelector('[data-pipeline-modal-close]')?.addEventListener('click', closePipelineModal);
            pipelineModal?.addEventListener('click', event => {
                if (event.target === pipelineModal) closePipelineModal();
            });

            const updateAddressCount = () => {
                if (!profileAddress || !profileAddressCount) return;
                profileAddressCount.textContent = `${profileAddress.value.length}/250`;
            };

            profileAddress?.addEventListener('input', updateAddressCount);
            updateAddressCount();

            settings.querySelector('[data-working-hours-toggle]')?.addEventListener('click', () => {
                workingHoursPanel?.classList.toggle('open');
            });

            profileForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!profileForm.reportValidity()) return;
                const button = settings.querySelector('[data-profile-save]');
                if (!button) return;
                const label = button.textContent;
                button.textContent = 'Saved';
                window.setTimeout(() => {
                    button.textContent = label;
                }, 1200);
            });

            const updatePropertyCount = () => {
                const rows = propertyTableBody?.querySelectorAll('[data-property-row]').length || 0;
                if (propertyCount) propertyCount.textContent = `${rows}/40`;
            };

            const renumberProperties = () => {
                propertyTableBody?.querySelectorAll('[data-property-row]').forEach((row, index) => {
                    row.cells[0].textContent = String(index + 1);
                });
                updatePropertyCount();
            };

            const updatePropertyNameCount = () => {
                if (!propertyNameInput || !propertyNameCount) return;
                propertyNameCount.textContent = `${propertyNameInput.value.length}/60`;
            };

            const openPropertyModal = row => {
                activePropertyRow = row || null;
                const isEdit = Boolean(row);
                if (propertyModalTitle) propertyModalTitle.textContent = isEdit ? 'Edit Custom Column' : 'Add Custom Property';
                if (propertyNameInput) {
                    propertyNameInput.value = row?.querySelector('[data-property-name]')?.textContent.trim() || '';
                    propertyNameInput.placeholder = isEdit ? '' : 'Enter';
                }
                if (propertyTypeInput) {
                    propertyTypeInput.disabled = isEdit;
                    propertyTypeInput.value = row?.querySelector('[data-property-type]')?.textContent.trim() || '';
                }
                updatePropertyNameCount();
                propertyModal?.classList.add('open');
                propertyModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                propertyNameInput?.focus();
            };

            const closePropertyModal = () => {
                activePropertyRow = null;
                propertyModal?.classList.remove('open');
                propertyModal?.setAttribute('aria-hidden', 'true');
                if (propertyTypeInput) propertyTypeInput.disabled = false;
                setScrollLock();
            };

            const buildPropertyActions = name => {
                const actions = document.createElement('span');
                actions.className = 'property-actions';
                actions.innerHTML = `
                    <label class="retry-switch" aria-label="Toggle property">
                        <input type="checkbox" checked>
                        <span></span>
                    </label>
                    <button type="button" class="property-icon-btn" data-property-edit aria-label="Edit property">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button type="button" class="property-icon-btn" data-property-delete aria-label="Delete property">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
                actions.querySelector('[data-property-edit]')?.setAttribute('aria-label', `Edit ${name}`);
                actions.querySelector('[data-property-delete]')?.setAttribute('aria-label', `Delete ${name}`);
                actions.querySelector('.retry-switch')?.setAttribute('aria-label', `Toggle ${name}`);
                return actions;
            };

            const appendPropertyRow = (name, type) => {
                if (!propertyTableBody) return;
                const row = propertyTableBody.insertRow();
                row.dataset.propertyRow = '';
                row.insertCell();
                const nameCell = row.insertCell();
                nameCell.dataset.propertyName = '';
                nameCell.textContent = name;
                const typeCell = row.insertCell();
                typeCell.dataset.propertyType = '';
                typeCell.textContent = type;
                row.insertCell().append(buildPropertyActions(name));
                renumberProperties();
            };

            settings.querySelector('[data-property-add]')?.addEventListener('click', () => openPropertyModal());
            settings.querySelector('[data-property-close]')?.addEventListener('click', closePropertyModal);
            propertyNameInput?.addEventListener('input', updatePropertyNameCount);

            propertyModal?.addEventListener('click', event => {
                if (event.target === propertyModal) closePropertyModal();
            });

            propertyTableBody?.addEventListener('click', event => {
                const row = event.target.closest('[data-property-row]');
                if (!row) return;
                if (event.target.closest('[data-property-edit]')) {
                    openPropertyModal(row);
                }
                if (event.target.closest('[data-property-delete]')) {
                    row.remove();
                    renumberProperties();
                }
            });

            propertyForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!propertyForm.reportValidity()) return;
                const name = propertyNameInput?.value.trim();
                const type = propertyTypeInput?.value;
                if (!name || !type) return;
                if (activePropertyRow) {
                    const nameCell = activePropertyRow.querySelector('[data-property-name]');
                    if (nameCell) nameCell.textContent = name;
                    activePropertyRow.querySelector('[data-property-edit]')?.setAttribute('aria-label', `Edit ${name}`);
                    activePropertyRow.querySelector('[data-property-delete]')?.setAttribute('aria-label', `Delete ${name}`);
                    activePropertyRow.querySelector('.retry-switch')?.setAttribute('aria-label', `Toggle ${name}`);
                } else {
                    appendPropertyRow(name, type);
                }
                closePropertyModal();
            });

            updatePropertyCount();

            const openRetryLogicModal = row => {
                activeRetryRow = row || activeRetryRow;
                retryLogicModal?.classList.add('open');
                retryLogicModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                retryCount?.focus();
            };

            const closeRetryLogicModal = () => {
                retryLogicModal?.classList.remove('open');
                retryLogicModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const openRetryReasonModal = row => {
                activeRetryRow = row || activeRetryRow;
                if (retryReasonInput) {
                    retryReasonInput.value = activeRetryRow?.querySelector('.retry-reason')?.textContent.trim() || '';
                }
                retryReasonModal?.classList.add('open');
                retryReasonModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                retryReasonInput?.focus();
            };

            const closeRetryReasonModal = () => {
                retryReasonModal?.classList.remove('open');
                retryReasonModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const closeRetryMenu = () => {
                retryMenu?.classList.remove('open');
            };

            const openRetryMenu = (button, row) => {
                if (!retryMenu) return;
                activeRetryRow = row;
                const rect = button.getBoundingClientRect();
                const width = 104;
                const height = 90;
                retryMenu.style.left = `${Math.min(Math.max(8, rect.right - width + 12), window.innerWidth - width - 8)}px`;
                retryMenu.style.top = `${Math.min(Math.max(8, rect.bottom - 6), window.innerHeight - height - 8)}px`;
                retryMenu.classList.add('open');
            };

            retryTable?.addEventListener('click', event => {
                const setup = event.target.closest('[data-retry-setup]');
                if (setup) {
                    openRetryLogicModal(setup.closest('[data-retry-row]'));
                    return;
                }

                const menuToggle = event.target.closest('[data-retry-action-toggle]');
                if (menuToggle) {
                    event.stopPropagation();
                    const row = menuToggle.closest('[data-retry-row]');
                    if (retryMenu?.classList.contains('open') && activeRetryRow === row) closeRetryMenu();
                    else openRetryMenu(menuToggle, row);
                }
            });

            retryMenu?.addEventListener('click', event => {
                const action = event.target.closest('[data-retry-menu-action]')?.dataset.retryMenuAction;
                if (!action || !activeRetryRow) return;
                if (action === 'edit') openRetryReasonModal(activeRetryRow);
                if (action === 'delete') activeRetryRow.remove();
                closeRetryMenu();
            });

            retryLogicModal?.addEventListener('click', event => {
                if (event.target === retryLogicModal || event.target.closest('[data-retry-logic-close]')) {
                    closeRetryLogicModal();
                }
            });

            retryLogicForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!retryLogicForm.reportValidity()) return;
                const rows = settings.querySelector('[data-retry-apply-all]')?.checked
                    ? settings.querySelectorAll('[data-retry-row]')
                    : [activeRetryRow].filter(Boolean);
                rows.forEach(row => {
                    row.dataset.retryLogic = `${retryCount?.value || 5} times / ${retryInterval?.value || 1} ${retryUnit?.value || 'Hours'}`;
                    const toggle = row.querySelector('[data-retry-toggle]');
                    if (toggle) toggle.checked = true;
                });
                closeRetryLogicModal();
            });

            retryReasonModal?.addEventListener('click', event => {
                if (event.target === retryReasonModal || event.target.closest('[data-retry-reason-close]')) {
                    closeRetryReasonModal();
                }
            });

            retryReasonForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!activeRetryRow || !retryReasonForm.reportValidity()) return;
                const name = retryReasonInput?.value.trim();
                const reasonLabel = activeRetryRow.querySelector('.retry-reason');
                if (!name || !reasonLabel) return;
                reasonLabel.textContent = name;
                activeRetryRow.dataset.retryReason = name;
                closeRetryReasonModal();
            });

            const getPriorityDropTarget = (list, y) => {
                const candidates = [...list.querySelectorAll('[data-priority-item]:not(.dragging)')];
                return candidates.reduce((closest, item) => {
                    const rect = item.getBoundingClientRect();
                    const offset = y - rect.top - rect.height / 2;
                    if (offset < 0 && offset > closest.offset) return { offset, item };
                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, item: null }).item;
            };

            const clearPriorityDropState = () => {
                priorityList?.classList.remove('drop-at-end');
                priorityList?.querySelectorAll('[data-priority-item]').forEach(item => item.classList.remove('drag-over'));
            };

            const removePriorityDragGhost = () => {
                priorityDragGhost?.remove();
                priorityDragGhost = null;
            };

            priorityList?.addEventListener('dragstart', event => {
                const item = event.target.closest('[data-priority-item]');
                if (!item) return;
                draggingPriorityItem = item;
                item.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', item.textContent.trim());
                const rect = item.getBoundingClientRect();
                priorityDragGhost = item.cloneNode(true);
                priorityDragGhost.classList.remove('dragging', 'drag-over');
                priorityDragGhost.classList.add('priority-drag-ghost');
                priorityDragGhost.style.width = `${rect.width}px`;
                settings.append(priorityDragGhost);
                event.dataTransfer.setDragImage(priorityDragGhost, 26, Math.round(rect.height / 2));
            });

            priorityList?.addEventListener('dragover', event => {
                if (!draggingPriorityItem) return;
                event.preventDefault();
                const target = getPriorityDropTarget(priorityList, event.clientY);
                clearPriorityDropState();
                if (target) {
                    target.classList.add('drag-over');
                } else {
                    priorityList.classList.add('drop-at-end');
                }
            });

            priorityList?.addEventListener('drop', event => {
                if (!draggingPriorityItem) return;
                event.preventDefault();
                const target = getPriorityDropTarget(priorityList, event.clientY);
                if (target) priorityList.insertBefore(draggingPriorityItem, target);
                else priorityList.append(draggingPriorityItem);
                clearPriorityDropState();
            });

            priorityList?.addEventListener('dragend', () => {
                draggingPriorityItem?.classList.remove('dragging');
                clearPriorityDropState();
                removePriorityDragGhost();
                draggingPriorityItem = null;
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('[data-user-actions-toggle]') && !event.target.closest('[data-user-actions-menu]')) {
                    closeUserActions();
                }
                if (!event.target.closest('[data-retry-action-toggle]') && !event.target.closest('[data-retry-menu]')) {
                    closeRetryMenu();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key !== 'Escape') return;
                closeUserActions();
                closeUserModal();
                closeCampaignDetailsModal();
                closePipelineModal();
                closeRetryMenu();
                closeRetryLogicModal();
                closeRetryReasonModal();
                closePropertyModal();
            });

            const modalPortal = document.createElement('div');
            modalPortal.className = 'calling-crm-settings crm-modal-portal';
            document.body.append(modalPortal);
            modalBackdrops.forEach(backdrop => modalPortal.append(backdrop));
        })();
