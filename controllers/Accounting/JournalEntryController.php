<?php
namespace App\Controllers\Accounting;

class JournalEntryController {

    public function index() {
        // TODO: List all journal entries, filter by financial year
    }

    public function create() {
        // TODO: Return view for creating a double-entry journal entry
    }

    public function store() {
        // TODO: Validate debits equal credits
        // TODO: Save entry to journal_entries and journal_entry_lines within a DB transaction
    }

    public function show($id) {
        // TODO: Display details of a specific journal entry
    }
    
    public function approve($id) {
        // TODO: Transition status from draft to posted and update chart of accounts balances
    }
}
