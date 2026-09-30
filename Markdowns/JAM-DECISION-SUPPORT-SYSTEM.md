# Intelligent Integration: AI Chatbot "JAM" and Decision Support System

## Overview
The system features a dual-layer artificial intelligence and automated decision support architecture specifically designed for the Cagayan State University Student Conduct Management System.

## Implementation Date
August 10, 2026

## What Was Implemented

### 1. "JAM" AI Policy Assistant (Student Facing)
**Status**: ✅ Implemented

JAM is the official CSU Student Conduct Policy Assistant. Built upon Anthropic's Claude model, it operates through a Laravel proxy (`StudentChatController.php`) to help students understand the 58 CSU offense rules.
- Rebranded from "AI Assistant" to "JAM AI Chatbot".
- Implemented English and Filipino system prompts customized for empathy and policy compliance.
- Restricts the AI to strict bounds (only the provided knowledge base).
- Prevents the AI from passing judgment and mandates escalation to the OSA for severe offenses.

### 2. Decision Support Algorithm (Tribunal / Staff Facing)
**Status**: ✅ Implemented

A fully deterministic, keyword-driven rule engine designed to assist OSDW Staff and Tribunal Members by analyzing complaint narratives and recommending potential violations and sanctions.
- **Database Architecture**: Created `decision_rules` and `decision_keywords` tables to allow the decision matrix to be configurable without code changes.
- **Service Integration**: Implemented `DecisionSupportService` to evaluate text using a weighted-sum matching algorithm.
- **Automated Logging**: Connected the service directly to the `StaffIncidentController::store()` method so that every new incident report is automatically analyzed.
- **Dashboard Support**: Upgraded the Tribunal/Staff incident detail view (`report-show.blade.php`) to cleanly display the decision support panel, showing the detected violation, matching terms, recommended sanction, and severity.

## Architecture

```
Student/Staff submits complaint
        ↓
Complaint text
        ↓
Decision Support Service
        ↓
Text preprocessing
        ↓
Keyword / phrase matching (Weighted)
        ↓
Decision Algorithm Matrix (Database)
        ↓
Recommended violation & sanction attached to Incident Report
        ↓
Tribunal Dashboard UI Displays Recommendation
```

## Database Models & Storage
- `DecisionRule`: Represents an actionable rule (e.g., "Cheating", "Violence").
- `DecisionKeyword`: Represents the triggers for a rule (e.g., "exam", "copy", "fight") along with assigned weights.
- `IncidentReport`: Extended with JSON columns and relational fields to store `recommended_violation`, `recommended_sanction`, `severity`, `decision_score`, and `matched_keywords`.

## Key Behavioral Constraints
1. **Never Replaces Humans**: The system is explicitly designed with UI warnings stating: *"Recommendation only. Final decision must be made by the Tribunal."*
2. **Rule Configurable**: Because the matching matrix lives in the database rather than hardcoded logic, administrators can tweak keyword weights over time as the system is used.

---
**Implementation Status**: ✅ Implemented
