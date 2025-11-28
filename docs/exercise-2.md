# Exercise 2 — Migrations no Yii2

Branch: `exercise-2`

## Objetivo
Criar migrações para implementar a futura funcionalidade de **permitir que usuários vinculem uma “pessoa” a um “cliente”** em um relacionamento `many-to-many`:


## Checklist

- [x] Criar migration mYYYYMMDD_HHMMSS_create_person_table:
  - [x] Tabela person com colunas propostas (inclui first_name, birthdate).
- [x] Criar migration mYYYYMMDD_HHMMSS_create_customer_person_table:
  - [x] Tabela customer_person (customer_id, person_id, relationship, timestamps).
  - [x] PK composta (customer_id, person_id) e FKs com CASCADE.

## Fundamentos

- Atributo relationship no pivot: a relação “marido/esposa/tio/filho/etc.” é contextual ao cliente; a mesma person pode ser “filho” de um cliente e “sobrinho” de outro.
- Separar Person da tabela customer evita duplicidade de dados e permite reuso em vários clientes (N:N).
- Timestamps mantêm rastreabilidade (seguem padrão das tabelas existentes).
