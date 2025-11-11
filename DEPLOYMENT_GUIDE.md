# Deployment Guide

## Quick Overview

Your SDK is ready to publish! This guide explains version management and deployment.

---

## 📦 Publishing to Packagist (First Release)

### Step 1: Create GitHub Repository
```bash
git init
git add .
git commit -m "Initial release v1.0.0"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/laravel-payment-sdk.git
git push -u origin main
```

### Step 2: Create Version Tag
```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### Step 3: Submit to Packagist
1. Go to https://packagist.org/packages/submit
2. Enter your repository URL
3. Submit

### Step 4: Setup Auto-Update (Important!)
1. In Packagist, go to your package settings
2. Copy the webhook URL
3. Add it to GitHub: Settings → Webhooks → Add webhook
4. This ensures automatic updates when you push new versions

---

## 🔄 Version Management (Semantic Versioning)

### Version Format: MAJOR.MINOR.PATCH (e.g., 1.2.3)

| Change Type | Bump | Example | When to Use |
|-------------|------|---------|-------------|
| **Bug Fix** | PATCH | 1.0.0 → 1.0.1 | Fix bugs, no new features |
| **New Feature** | MINOR | 1.0.0 → 1.1.0 | Add features, backward compatible |
| **Breaking Change** | MAJOR | 1.0.0 → 2.0.0 | Change existing behavior, not backward compatible |

---

## 🚀 Releasing New Versions

### Patch Release (Bug Fix)
```bash
# 1. Fix the bug in your code
# 2. Update CHANGELOG.md
# 3. Commit and tag
git add .
git commit -m "Fix: bug description"
git push origin main
git tag -a v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1
```

**Result:** Users get this update automatically with `composer update`

---

### Minor Release (New Feature)
```bash
# 1. Add the new feature
# 2. Update CHANGELOG.md and README.md (if needed)
# 3. Commit and tag
git add .
git commit -m "Feature: new feature description"
git push origin main
git tag -a v1.1.0 -m "Release version 1.1.0"
git push origin v1.1.0
```

**Result:** Users get this update automatically with `composer update`

---

### Major Release (Breaking Change)
```bash
# 1. Create maintenance branch for old version
git checkout -b 1.x
git push origin 1.x

# 2. Make breaking changes on main branch
git checkout main
# ... make changes ...

# 3. Update CHANGELOG.md with upgrade instructions
# 4. Commit and tag
git add .
git commit -m "Release version 2.0.0"
git push origin main
git tag -a v2.0.0 -m "Release version 2.0.0"
git push origin v2.0.0
```

**Result:** Users must explicitly upgrade with `composer require basgate/laravel-payment-sdk:^2.0`

---

## 👥 How Users Install and Update

### Initial Installation
```bash
composer require basgate/laravel-payment-sdk
```
This installs the latest stable version.

### Getting Updates
```bash
composer update basgate/laravel-payment-sdk
```

**What they get automatically:**
- ✅ Patch versions (1.0.1, 1.0.2, etc.)
- ✅ Minor versions (1.1.0, 1.2.0, etc.)
- ❌ Major versions (2.0.0) - requires explicit upgrade

This is controlled by the `^` constraint in their `composer.json`:
```json
{
    "require": {
        "basgate/laravel-payment-sdk": "^1.0"
    }
}
```

The `^1.0` means: "Install 1.x versions, but not 2.0"

---

## 📝 Best Practices

### Before Each Release
1. ✅ Test your changes thoroughly
2. ✅ Update CHANGELOG.md with changes
3. ✅ Ensure composer.json is valid: `composer validate`
4. ✅ Commit all changes before tagging

### CHANGELOG.md Format
```markdown
## [1.0.1] - 2024-01-15

### Fixed
- Bug description

## [1.0.0] - 2024-01-10

### Added
- Initial release
```

### Git Tags
- Always use annotated tags: `git tag -a v1.0.0 -m "Release version 1.0.0"`
- Always push tags: `git push origin v1.0.0`
- Never delete published tags

---

## ⚠️ Important Notes

### No Version in composer.json
Your `composer.json` does not include a version field. This is correct! Packagist reads versions from your Git tags.

### No Hard Dependencies
Your `composer.json` has empty `require: {}`. This prevents conflicts with other packages. Laravel's packages are provided by the user's installation.

### Backward Compatibility
For versions 1.x:
- ✅ You can add new methods
- ✅ You can add optional parameters
- ✅ You can fix bugs
- ❌ Don't remove methods
- ❌ Don't change method signatures
- ❌ Don't change behavior of existing features

---

## 🔧 Troubleshooting

### Package not updating on Packagist?
- Check webhook was triggered on GitHub
- Manually trigger update from Packagist settings page

### Users can't install?
- Verify Git tag was pushed: `git tag -l`
- Check tag format: must be `vX.Y.Z` (e.g., `v1.0.0`)
- Ensure repository is public

### Need to fix a bad release?
- Release a new patch version immediately
- Update CHANGELOG.md noting the fix
- Never delete or modify existing tags

---

## 📊 After Publishing

### Monitor Your Package
- Check Packagist download statistics
- Respond to GitHub issues
- Review pull requests

### Maintenance Schedule
- **Weekly:** Check for new issues
- **Monthly:** Review dependencies
- **Per bug report:** Release patch version
- **Per feature request:** Plan minor version

---

## ✅ Ready Checklist

Before your first release:
- [ ] Code is tested and working
- [ ] README.md has clear installation instructions
- [ ] CHANGELOG.md has v1.0.0 entry with today's date
- [ ] LICENSE file exists
- [ ] GitHub repository created and code pushed
- [ ] composer.json is valid

Then:
1. Create tag `v1.0.0`
2. Submit to Packagist
3. Set up webhook
4. Done! 🎉

---

**Your package will be live at:**
- Packagist: `https://packagist.org/packages/basgate/laravel-payment-sdk`
- Installation: `composer require basgate/laravel-payment-sdk`
