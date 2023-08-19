export const authorMxn = {
	getDefaultNameDataObj() {
		return {
			"name": "",
			"firstName": "",
			"displayName": "",
			"displayNameModified": false,
			"onlyLastName": false,
			"isNew": false,
			"potentialContributors": {
				"found": [],
				"loading": false,
				"error": "",
				"popoverVisible": false
			},
			"existingContributor": null,
		};
	},
	getAuthorNameData(authorTxt) {
		let author = this.getDefaultNameDataObj();

		let authorNameParts = authorTxt.trim().split(" ");
		author['name'] = authorNameParts.at(-1).trim();
		author['displayName'] = authorTxt;
		author['displayNameModified'] = authorNameParts > 1 && author['name'] != "";
		author['onlyLastName'] = authorNameParts > 1;

		if (authorNameParts.length > 1) {
			author['firstName'] = authorNameParts.slice(0, -1).join(' ').trim();
			author['onlyLastName'] = false;
		}
		return author;
	},
	getContributorList(contributors, isPlain) {
		let hasMoreContributors = false;
		let authorList = contributors.map((authorPlain) => {
			let author = authorPlain;
			if (isPlain) {
				// plain assumes that the data we get does not contain
				// other information apart from names so it can safely
				// been thrown away
				author = this.getDefaultNameDataObj();
				author['name'] = authorPlain['name'];
				author['firstName'] = authorPlain['firstName'];
				author['displayName'] = authorPlain['displayName'];
			}
			if (author['name'].endsWith("…")) {
				author['name'] = author['name'].slice(0, -1);
				hasMoreContributors = true;
			}
			if (author['firstName'].endsWith("…")) {
				author['firstName'] = author['firstName'].slice(0, -1);
				hasMoreContributors = true;
			}
			if (author['displayName'].endsWith("…")) {
				author['displayName'] = author['displayName'].slice(0, -1);
				hasMoreContributors = true;
			}
			return author;
		});
		if (hasMoreContributors) {
			let author = this.getDefaultNameDataObj();
			author['name'] = "…";
			author['firstName'] = "…";
			author['displayName'] = "…";
			author['isNew'] = true;
			author['onlyLastName'] = true;
			authorList.push(author);
		}
		return authorList;
	},
	getContributorListFromTxt(authorTxt) {
		let authorList = authorTxt.split(",");
		authorList = authorList.map((author) => {
			author = author.trim()
			let authorData = this.getAuthorNameData(author)
			authorData.isNew = true;
			return authorData;
		});
		return this.getContributorList(authorList, false);
	}
}