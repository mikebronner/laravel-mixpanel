# Quality Assurance
These steps should be converted to automated tests at some point.

## Checklist
### JavaScript Library
- [ ] Is blade partial included in page source?
- [ ] Is blade partial working, and not throwing console errors or warnings?
- [ ] Does the elixir version compile without issue?
- [ ] Is the compiled library included on the page, and not throwing errors or
 warnings?

### PHP Library
- [ ] When a new user registers on the site, is both the session and the user
 tracked?
- [ ] When a user logs out, is it tracked?
- [ ] When a user attempts to log in, is it tracked?
- [ ] When a user successfully logs in, is it tracked?
- [ ] When a user views a page, is it tracked?
