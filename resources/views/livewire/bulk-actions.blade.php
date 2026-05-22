<div class="bulk-actions-wrapper">
    <!-- Bulk Actions Button with Dropdown -->
    <div class="bulk-actions-container">
        <button 
            type="button" 
            class="bulk-actions-btn"
            wire:click="toggleDropdown"
            :class="{ 'disabled': selectedCount === 0 }"
            :disabled="selectedCount === 0"
        >
            <span>Bulk Actions</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        @if($showDropdown)
            <div class="bulk-actions-dropdown" wire:click.outside="toggleDropdown">
                <div class="dropdown-header">Choose Bulk Actions</div>
                
                <button 
                    type="button"
                    class="dropdown-item"
                    wire:click="selectAction('update')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Update
                </button>

                <button 
                    type="button"
                    class="dropdown-item"
                    wire:click="selectAction('delete')"
                    onclick="return confirm('Are you sure you want to delete {{ selectedCount }} leads?')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                    Delete
                </button>

                <button 
                    type="button"
                    class="dropdown-item"
                    wire:click="selectAction('move')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="5 12 3 12 12 3 21 12 19 12"></polyline>
                        <polyline points="5 12 3 12 12 21 21 12 19 12"></polyline>
                    </svg>
                    Move to Other Campaign
                </button>

                <button 
                    type="button"
                    class="dropdown-item"
                    wire:click="selectAction('copy')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                    Copy to Other Campaign
                </button>

                <button 
                    type="button"
                    class="dropdown-item"
                    wire:click="selectAction('close')"
                    onclick="return confirm('Are you sure you want to close {{ selectedCount }} leads?')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Close Leads
                </button>
            </div>
        @endif
    </div>

    <!-- Selected count display -->
    @if($selectedCount > 0)
        <div class="selected-count">
            <span>{{ $selectedCount }} lead{{ $selectedCount !== 1 ? 's' : '' }} selected</span>
        </div>
    @endif
</div>

<style>
    .bulk-actions-wrapper {
        position: relative;
        display: inline-block;
    }

    .bulk-actions-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 7px;
        border: 1.5px solid #e3e6ef;
        background: #ffffff;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.18s;
        font-family: 'DM Sans', sans-serif;
    }

    .bulk-actions-btn:hover:not(.disabled) {
        border-color: #5b6af5;
        color: #5b6af5;
    }

    .bulk-actions-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .bulk-actions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        background: white;
        border: 1px solid #e3e6ef;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(26, 29, 46, 0.1);
        min-width: 200px;
        z-index: 1000;
        animation: slideDown 0.2s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-header {
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 1px solid #f0f2f7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        background: none;
        border: none;
        text-align: left;
        font-size: 13px;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s;
        font-family: 'DM Sans', sans-serif;
    }

    .dropdown-item:hover {
        background: #f8f9fc;
        color: #5b6af5;
    }

    .dropdown-item svg {
        flex-shrink: 0;
        color: currentColor;
    }

    .selected-count {
        display: inline-block;
        margin-left: 12px;
        padding: 6px 10px;
        background: #eef0ff;
        color: #5b6af5;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
</style>
