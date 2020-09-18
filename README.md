# rudl-vault
GitOps Key vault utility



## Type of secrets

- Inline secrets: `{RSEC.<keyId>.<encoded data>}`
- Block secrets:
  ```
  ---- BEGIN RUDL VAULT SECRET ----
  key_id: wurst
  <encoded data>
  ----- END RUDL VAULT SECRET -----
  ```
  
- Secret reference: Reference to a secret specified in
  .rudl-vault.json secrets section
  `{RREF.<secret_name>}`