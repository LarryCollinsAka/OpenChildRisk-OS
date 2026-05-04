# ================================================
# OpenChildRisk OS — Risk Engine Dockerfile
# ================================================
# Python 3.11 slim for minimal image size
# Critical for low-bandwidth deployments
# in Sub-Saharan Africa field environments

FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Copy requirements first for Docker layer caching
# This means pip install only reruns when
# requirements.txt changes — faster rebuilds
COPY requirements.txt .

# Install dependencies
# No cache to keep image size small
RUN pip install --no-cache-dir -r requirements.txt

# Copy all application code
COPY . .

# Expose port 8001
# Laravel communicates with this service on this port
EXPOSE 8001

# Start the FastAPI service
# Host 0.0.0.0 required for Docker networking
# Reload disabled in production
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8001", "--reload"]