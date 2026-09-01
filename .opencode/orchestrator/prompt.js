// guarana orchestrator — prompt builders (host-independent).
// Builds the small always-on orchestration block (progressive disclosure) and
// the per-step directive naming which guarana skill to load. The full skill
// body is NOT included here — it stays in the skill tool, loaded on selection.

import { STATE_SKILL, skillForState } from './state.js';

const SKILL_LINES = Object.entries(STATE_SKILL)
  .filter(([, s]) => s)
  .map(([state, s]) => `- ${state} -> load \`guarana:${s}\``)
  .join('\n');

// Small, always-resident context block. Deliberately short.
export function buildSystemBlock(workflow) {
  const skill = skillForState(workflow.state);
  return [
    '## Guarana workflow (automatic engineering loop)',
    '',
    'You are inside the guarana loop. It decides the next step for you based on',
    'the persisted workflow state; you execute it. State is the source of truth',
    'on disk at `.specs/state/workflow.json`.',
    '',
    `Current state: **${workflow.state}**`,
    `Active task: ${workflow.activeTask || '(none)'}`,
    `Goal: ${workflow.goal || '(none)'}`,
    '',
    'State → skill to load:',
    SKILL_LINES,
    '',
    'Rules:',
    '- Load the skill for the current state via the `skill` tool; follow its body.',
    '- When the current step is done, call `workflow_tick` with the matching action',
    '  to advance the state machine (e.g. code_complete, verify_pass, verify_fail).',
    '- Use `workflow_get` to read the latest state.',
    '- A user typing `guarana:<skill>` forces that step (escape hatch).',
    '- Explicit `guarana:plan` remains available; otherwise planning runs automatically.',
    '',
    skill ? `Next action: load \`guarana:${skill}\` and follow it.` : 'Idle: await a task, or a `guarana:<skill>` command.',
  ].join('\n');
}

// Per-step directive appended after the block so the model knows exactly which
// skill to select and how to report completion. Kept minimal.
export function buildDirective(workflow) {
  const skill = skillForState(workflow.state);
  if (!skill) return '';
  const action = {
    planning: 'plan_complete',
    building: 'run_start',
    coding: 'code_complete',
    verifying: 'verify_pass|verify_fail',
    debugging: 'debug_complete',
  }[workflow.state];
  return [
    '',
    `Workflow step: **${workflow.state}**. Load \`guarana:${skill}\` and follow it.`,
    action
      ? `When the step completes, call \`workflow_tick\` with action \`${action}\`.`
      : '',
  ]
    .filter(Boolean)
    .join('\n');
}