# Makefile for managing docker-compose services

# Define the Docker Compose file (default to docker-compose.yml)
DOCKER_COMPOSE = docker-compose

# Define the target services (default to 'up')
DOCKER_COMPOSE_UP = $(DOCKER_COMPOSE) up -d --build
DOCKER_COMPOSE_DOWN = $(DOCKER_COMPOSE) down
DOCKER_COMPOSE_PRUNE = $(DOCKER_COMPOSE) down --volumes --remove-orphans

# Target: Start services with build
start:
	$(DOCKER_COMPOSE_UP)

# Target: Stop running services
stop:
	$(DOCKER_COMPOSE_DOWN)

# Target: Stop services and prune volumes
clean:
	$(DOCKER_COMPOSE_PRUNE)

# Optionally, you can also add a target for rebuilding without starting
rebuild:
	$(DOCKER_COMPOSE) build
