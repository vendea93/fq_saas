# FlowQuest SaaS Module Repository

This repository contains the main FQ SaaS module for the FlowQuest CRM system.

## Overview
The FQ SaaS module is the core component that enables the multi-tenant architecture of FlowQuest CRM. It provides the infrastructure for managing multiple client instances, user authentication, subscription management, and tenant-specific configurations.

## Repository Branches
- `main` - Original clean FQ SaaS code from backup (2026-04-21)
- `server-version` - Current server modifications with all updates

## Module Structure
```
fq_saas/
├── fq_saas.php             # Main module file
├── assets/                 # CSS, JS, images for admin/client interfaces
├── config/                 # Configuration files and settings
├── controllers/           # Admin and client controllers
├── helpers/               # Helper functions
├── hooks/                 # System hooks and integrations
├── languages/             # Multi-language support files
├── libraries/             # Custom libraries and integrations
├── migrations/            # Database migrations and seeds
├── models/                # Data models
├── tools/                 # Administrative tools and scripts
├── views/                 # Template files for admin and client interfaces
├── vendor/                # Third-party dependencies
└── README.md              # This file
```

## Key Features
1. **Multi-tenant Architecture** - Support for multiple client instances
2. **Subscription Management** - Plan-based subscription system
3. **User Authentication** - Secure login and access control
4. **Instance Management** - Creation, configuration, and monitoring of client instances
5. **Theme System** - Customizable themes for different industries
6. **Integration Tools** - APIs and webhooks for third-party services
7. **Demo System** - Automated demo instance creation and management
8. **Reporting** - Analytics and usage statistics

## Configuration
The module uses several configuration files:
- `config/app.php` - Main application settings
- `config/demo_instances.php` - Demo instance configurations
- `config/integrations/` - Third-party service configurations

## AI Agent Instructions
1. **Branch Management**:
   - `main` branch contains original backup code
   - `server-version` branch contains current server modifications
   - Create feature branches from `server-version` for development

2. **Development Guidelines**:
   - Follow Perfex CRM module structure conventions
   - Maintain backward compatibility
   - Use consistent naming patterns for functions and variables
   - Document all API endpoints and integrations
   - Test multi-tenant scenarios thoroughly

3. **Version Tracking**:
   - Check commit history for update logs
   - Compare branches to see differences
   - Use git diff to analyze specific changes
   - Monitor database migration files for schema changes

4. **Deployment Notes**:
   - Module path: `/var/www/crm.flowquest.pl/modules/fq_saas/`
   - Dependencies are managed through composer.json
   - Database migrations must be run after updates
   - Test instance creation and deletion workflows

5. **Key Directories to Monitor**:
   - `migrations/` - Database schema changes
   - `assets/` - Frontend/UI changes
   - `controllers/` - API and business logic changes
   - `models/` - Data structure changes
   - `views/` - Template/UI changes
   - `tools/` - Administrative scripts and utilities

## Important Documentation
Key files for understanding the SaaS module:
- `README.md` - This file
- `FQ_SAAS_CHANGELOG_KOMENTARZ.md` - Detailed change log comments
- Migration files in `migrations/` directory
- Configuration files in `config/` directory

## Environment Setup
For development and testing:
1. Ensure Perfex CRM is installed and configured
2. Copy module to `/var/www/crm.flowquest.pl/modules/fq_saas/`
3. Activate module through CRM admin panel
4. Run database migrations
5. Configure settings in module administration

## Troubleshooting
Common issues and solutions:
1. **Instance Creation Failures** - Check database permissions and migration status
2. **Authentication Issues** - Verify configuration files and hook integrations
3. **Theme Problems** - Check asset paths and CSS variable usage
4. **Demo Instance Issues** - Validate seed data and configuration templates

Last updated: 2026-05-02