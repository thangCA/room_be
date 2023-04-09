FROM node:16-alpine

WORKDIR /usr/src/app

COPY package*.json ./

COPY yarn.lock /app

RUN yarn install

COPY . .

EXPOSE 3000

CMD ["yarn", "start"]