# /public/uploads/ Directory Structure

This directory is used for user-uploaded documents, particularly for KYC (Know Your Customer) verification. In a real production environment, these files would typically be stored in a more secure location or cloud storage service, but for development purposes, this structure is provided.

```
public/uploads/
├── .gitignore                     # Ignores user uploads but keeps directory structure
├── kyc/                           # KYC document uploads
│   ├── id_documents/              # Identity documents (passport, ID cards)
│   ├── proof_of_address/          # Proof of address documents
│   ├── selfies/                   # User verification selfies
│   └── additional/                # Additional verification documents
├── profile/                       # User profile images
│   └── avatars/                   # User avatars
└── temp/                          # Temporary uploads before verification
```

## .gitignore content

The `.gitignore` file in this directory should contain:

```
# Ignore all files in this directory
*

# Except for this .gitignore file
!.gitignore

# And except for the directory structure
!*/
!*/*/
```

This ensures that the directory structure is preserved in the repository without including any user-uploaded files.

## Security Considerations

In a production environment:

1. User-uploaded files should not be stored in the public directory
2. Consider using Laravel's storage system with appropriate access controls
3. For financial applications like Libertom, cloud storage with encryption would be recommended
4. Implement strict file validation for all uploads
5. Consider using a CDN or specialized service for document handling and storage

## Implementation Notes

For the Libertom platform, document uploads will be handled by the `DocumentUploadController` which will:

1. Validate the uploaded file (type, size, etc.)
2. Sanitize the filename
3. Move the file to the appropriate directory based on document type
4. Store the document metadata in the database
5. Handle document verification workflows

For KYC documents specifically, an additional layer of security and verification will be applied.