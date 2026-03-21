#!/bin/bash
# Generates a self-signed SSL certificate for fiqtest.
# Run this once on any new server before starting Docker.
#
# Usage:
#   bash docker/generate-certs.sh
#   bash docker/generate-certs.sh yourdomain.com   # for a domain
#   bash docker/generate-certs.sh 159.65.8.92      # for an IP

set -e

CN="${1:-localhost}"
CERT_DIR="$(dirname "$0")/certs"

mkdir -p "$CERT_DIR"

# Detect if CN is an IP or a domain
if [[ "$CN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    SAN="IP:$CN"
else
    SAN="DNS:$CN"
fi

echo "==> Generating self-signed certificate for: $CN"
echo "    SAN: $SAN"
echo "    Output: $CERT_DIR"

openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
    -keyout "$CERT_DIR/key.pem" \
    -out    "$CERT_DIR/cert.pem" \
    -subj   "/C=ID/ST=Indonesia/L=Jakarta/O=fiqtest/CN=$CN" \
    -addext "subjectAltName=$SAN"

chmod 600 "$CERT_DIR/key.pem"
chmod 644 "$CERT_DIR/cert.pem"

echo ""
echo "==> Done! Certificate valid for 10 years."
echo "    cert.pem : $CERT_DIR/cert.pem"
echo "    key.pem  : $CERT_DIR/key.pem"
echo ""
echo "==> Next: docker compose up -d --build --force-recreate nginx"
