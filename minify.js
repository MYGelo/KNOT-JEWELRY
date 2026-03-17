const fs = require('fs')
const path = require('path')
const {minify} = require('terser')
const postcss = require('postcss')
const nesting = require('postcss-nesting')
const cssnano = require('cssnano')

function getFiles(dir, ext) {
	let results = []
	if(!fs.existsSync(dir)) return results
	fs.readdirSync(dir).forEach(file => {
		const filePath = path.join(dir, file)
		const stat = fs.statSync(filePath)
		if(stat.isDirectory()) {
			results = results.concat(getFiles(filePath, ext))
		} else if(file.endsWith(ext)) {
			results.push(filePath)
		}
	})
	return results
}

async function minifyJS(dir) {
	const files = getFiles(dir, '.js')
	await Promise.all(
		files.map(async filePath => {
			const code = fs.readFileSync(filePath, 'utf8')
			try {
				const result = await minify(code)
				if(result.code) {
					fs.writeFileSync(filePath, result.code, 'utf8')
					console.log(`✅ Minified JS: ${filePath}`)
				}
			} catch(err) {
				console.error(`❌ JS error in ${filePath}:`, err.message)
			}
		})
	)
}

async function minifyCSS(dir) {
	const files = getFiles(dir, '.css')
	await Promise.all(
		files.map(async filePath => {
			const code = fs.readFileSync(filePath, 'utf8')
			try {
				const result = await postcss([nesting(), cssnano()])
					.process(code, {from: filePath})
				fs.writeFileSync(filePath, result.css, 'utf8')
				console.log(`✅ Minified CSS: ${filePath}`)
			} catch(err) {
				console.error(`❌ CSS error in ${filePath}:`, err.message)
			}
		})
	)
}

const foldersToMinify = [
	'./assets/js',
	'./assets/css',
	'./template-parts/gutenberg-blocks'
];

(async() => {
	for(const folder of foldersToMinify) {
		await minifyJS(folder)
		await minifyCSS(folder)
	}
	console.log('🎉 All JS/CSS processed successfully')
})()
