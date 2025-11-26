# Exercise 1 — Fundamentos Yii2 (registro de progresso)

Branch: `exercise-1`

## Objetivo
Implementar tela de login + criação de produto usando DDD Lite.

## Decisões iniciais
- Arquitetura: DDD Lite com camadas Domain / Application / Infrastructure.
- Local do Yii2: `src/Infrastructure/Yii`.
- Produto armazena: `id, name, type_id, price, code, created_at`.
- Tipos de produto: tabela `product_type` (criada via migration).
- Campo `code` será calculado no UseCase `CreateProduct`, persistido na entidade Product.

## Checklist
- [ ] Criar migrations iniciais (product_type, product)
- [ ] Gerar AR via Gii
- [ ] Criar Domain/Repository interface
- [ ] Implementar Repository na Infra (usando AR)
- [ ] Criar UseCase `CreateProduct`
- [ ] Criar Controller `ProductController` e views (login + create)
- [ ] Criar testes unitários para UseCase e Repository
