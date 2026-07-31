# The Purdia Foundry Manifesto

## Software Should Be Forged, Not Just Built

Most applications are built as products.

Purdia is built as a **foundry**.

Every feature begins its life inside the application, where it is designed, refined, and proven through real-world use before it earns the right to become a reusable module.

The dashboard is not the final product.

It is the forge.

---

## Build Once. Reuse Everywhere.

Every module starts its life inside Purdia.

It is designed, tested, refined, and battle-tested through real usage.

Only after it proves itself does it deserve to become a reusable module.

We do not extract ideas.

We extract software that has survived production.

---

## Modules Are Citizens, Not Folders.

A module is more than a directory.

A module owns its entire business capability:

* Domain
* Application
* Infrastructure
* Database
* Routes
* Service Provider
* Resources
* Tests

A module should describe a complete business concern.

If removing a module breaks unrelated parts of the system, the module boundary is wrong.

---

## Independence Is a Feature.

A module should know as little as possible about other modules.

Dependencies must always be intentional.

Circular dependencies are architectural failures.

The dependency graph must remain clean, predictable, and easy to reason about.

Loose coupling is not an optimization.

It is a design requirement.

---

## Shared Is Foundation, Never Business.

Shared exists to provide infrastructure.

It may contain:

* Base classes
* Traits
* Value Objects
* Contracts
* Framework integrations

It must never become a place where business logic accumulates.

Business belongs to modules.

---

## Core Modules Are Blind.

Core modules provide capabilities.

They never consume them.

User does not know about Notes.

User does not know about Tasks.

User does not know about Finance.

Shared does not know about feature modules.

Feature modules depend on core.

Core depends on nothing.

This rule is non-negotiable.

---

## Architecture Is Enforced, Not Remembered.

Good architecture should never rely on discipline alone.

Every architectural rule should eventually become automation.

The system must be able to verify:

* Module boundaries
* Dependency rules
* Structural consistency
* Extractability

Architecture that cannot be verified will eventually decay.

---

## Extractability Is the Goal.

Every module should be developed as if it might leave this repository tomorrow.

That does not mean every module must become a package.

It means every module should always be capable of becoming one.

Extraction is the consequence of good architecture—not its purpose.

---

## Production Before Packaging.

Reusable software is not created by publishing packages.

Reusable software is created by solving real problems repeatedly.

Every module must first prove itself in production before it earns the right to be extracted.

Stability always comes before portability.

---

## A Module Owns Its Interface.

A module is not only backend code.

It owns everything required to deliver its capability:

* Pages
* Components
* Types
* API layer
* Styles

If the backend can be extracted but the frontend cannot, the module is incomplete.

Full-stack isolation is the standard.

---

## Modules Have Lifecycles.

Every module progresses through distinct stages:

1. **Incubating** — actively evolving, breaking changes are acceptable.
2. **Stable** — production-tested with a reliable public contract.
3. **Extractable** — fully isolated and ready to leave the foundry.

Extraction is earned through maturity, not ambition.

---

## Opinionated by Design.

Purdia is not a framework.

It does not attempt to satisfy every architecture, every coding style, or every preference.

Instead, it embraces a deliberate set of opinions:

* Laravel
* Domain-Driven Design
* Modular Architecture
* Convention over Configuration
* Pragmatism over Abstraction

These constraints are intentional.

Consistency creates maintainability.

---

## The Dashboard Is the Laboratory.

The dashboard is where modules are born.

Ideas become implementations.

Implementations become production software.

Production software becomes reusable modules.

The application continuously improves the library, and the library continuously improves the application.

They evolve together.

---

## Build for the Next Project.

Every architectural decision should answer one question:

> "Will this make the next project easier to build?"

If the answer is yes, it belongs in the foundry.

If not, it belongs only in the application.

---

## Our Principle

> We do not build reusable modules because we planned them.
>
> We build good software first.
>
> We earn confidence through production.
>
> Then we extract what has proven worthy of being reused.
