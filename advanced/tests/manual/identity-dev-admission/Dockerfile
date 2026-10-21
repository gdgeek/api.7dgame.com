# Public, independently buildable test runtime. No private application image,
# application configuration, vendor cache, database or credential is baked in.
FROM php:8.5-apache-bookworm
COPY --from=node:24-bookworm /usr/local/bin/node /usr/local/bin/node
RUN php -r 'foreach (["curl", "mbstring", "pdo_sqlite"] as $name) { if (!extension_loaded($name)) { throw new RuntimeException("Required fixture extension missing."); } }'
ENTRYPOINT ["node"]
